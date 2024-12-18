<?php

namespace NextBuild\ActivityTracker\Watchers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\Request;
use Illuminate\Http\Response as IlluminateResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\View\View;
use NextBuild\ActivityTracker\FormatModel;
use NextBuild\ActivityTracker\IncomingEntry;
use NextBuild\ActivityTracker\ActivityTracker;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;

use NextBuild\ActivityTracker\Storage\VisitorIpModel;
use NextBuild\ActivityTracker\Storage\VisitorRequestModel;

use Jenssegers\Agent\Agent;

class RequestWatcher extends Watcher
{
    public $SESSION_PATH = 'activity-tracker-session';

    /**
     * Register the watcher.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function register($app)
    {
        $app['events']->listen(RequestHandled::class, [$this, 'recordRequest']);
    }

    /**
     * Record an incoming HTTP request.
     *
     * @param  \Illuminate\Foundation\Http\Events\RequestHandled  $event
     * @return void
     */
    public function recordRequest(RequestHandled $event)
    {
        if (! ActivityTracker::isRecording() ||
            $this->shouldIgnoreHttpMethod($event) ||
            $this->shouldIgnoreStatusCode($event) ||
            $this->shouldIgnorePath($event->request)
        )
        {
            return;
        }

        $startTime = defined('LARAVEL_START') ? LARAVEL_START : $event->request->server('REQUEST_TIME_FLOAT');

        $ip = $event->request->header('X-Real-IP') ?? $event->request->ip();
        // $ip = '216.57.45.241';

        $visitor_ip = VisitorIpModel::where('ip_address', $ip)->first();
        if (!$visitor_ip) {

            $ip_details = Http::get("http://ip-api.com/json/{$ip}")->json();
            $agent = new \Jenssegers\Agent\Agent();

            $visitor_ip = VisitorIpModel::create([
                'uuid' => \Str::uuid(),
                'ip_address' => $ip,
                'timezone' => $ip_details['timezone'] ?? null,
                'country_code' => $ip_details['countryCode'] ?? null,
                'content' => [

                    'country' => $ip_details['country'] ?? null,
                    'region' => $ip_details['region'] ?? null,
                    'regionName' => $ip_details['regionName'] ?? null,
                    'city' => $ip_details['city'] ?? null,
                    'zip' => $ip_details['zip'] ?? null,
                    'latitude' => $ip_details['lat'] ?? null,
                    'longitude' => $ip_details['lon'] ?? null,

                    'device' => $agent->device(),
                    'platform' => $agent->platform(),
                    'platformVersion' => $agent->version($agent->platform()),
                    'browser' => $agent->browser(),
                    'browserVersion' => $agent->version($agent->browser()),

                ],
                'is_robot' => $agent->isRobot(),
            ]);

        }

        VisitorRequestModel::create([
            'uuid' => \Str::uuid(),
            'visitor_ip_uuid' => $visitor_ip->uuid,
            'type' => 'requests',
            'content' => [
                'uri' => str_replace($event->request->root(), '', $event->request->fullUrl()) ?: '/',
                'full_url' => $event->request->fullUrl(),
                'method' => $event->request->method(),
                'controller_action' => optional($event->request->route())->getActionName(),
                'middleware' => array_values(optional($event->request->route())->gatherMiddleware() ?? []),
                'headers' => $this->headers($event->request->headers->all()),
                'payload' => $this->payload($this->input($event->request)),
                'session' => $this->payload($this->sessionVariables($event->request)),
                'response_status' => $event->response->getStatusCode(),
                'response' => $this->response($event->response),
                'duration' => $startTime ? floor((microtime(true) - $startTime) * 1000) : null,
                'memory' => round(memory_get_peak_usage(true) / 1024 / 1024, 1),
            ],
            'created_at' => \Carbon\Carbon::now(),
        ]);
    }

    protected function clientContent()
    {
        $agent = new \Jenssegers\Agent\Agent();
        $device = $agent->device();
        $platform = $agent->platform();
        $browser = $agent->browser();
        $version = $agent->version($browser);
        $robot = $agent->robot();

        return [
            'device' => $device,
            'platform' => $platform,
            'browser' => $browser,
            'version' => $version,
            'robot' => $robot,
        ];
    }

    /**
     * Determine if the request should be ignored based on path.
     *
     * @param  mixed  $request
     * @return bool
     */
    protected function shouldIgnorePath($request)
    {
        $ignoredPaths = ['/css', '/js', '/images', '/fonts', '/vendor'];
        foreach ($ignoredPaths as $path) {
            if (str_starts_with($request->getRequestUri(), $path)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Determine if the request should be ignored based on its method.
     *
     * @param  mixed  $event
     * @return bool
     */
    protected function shouldIgnoreHttpMethod($event)
    {
        return in_array(
            strtolower($event->request->method()),
            collect($this->options['ignore_http_methods'] ?? [])->map(function ($method) {
                return strtolower($method);
            })->all()
        );
    }

    /**
     * Determine if the request should be ignored based on its status code.
     *
     * @param  mixed  $event
     * @return bool
     */
    protected function shouldIgnoreStatusCode($event)
    {
        return in_array(
            $event->response->getStatusCode(),
            $this->options['ignore_status_codes'] ?? []
        );
    }

    /**
     * Format the given headers.
     *
     * @param  array  $headers
     * @return array
     */
    protected function headers($headers)
    {
        $headers = collect($headers)
            ->map(fn ($header) => implode(', ', $header))
            ->all();

        return $this->hideParameters($headers,
            ActivityTracker::$hiddenRequestHeaders
        );
    }

    /**
     * Format the given payload.
     *
     * @param  array  $payload
     * @return array
     */
    protected function payload($payload)
    {
        return $this->hideParameters($payload,
            ActivityTracker::$hiddenRequestParameters
        );
    }

    /**
     * Hide the given parameters.
     *
     * @param  array  $data
     * @param  array  $hidden
     * @return mixed
     */
    protected function hideParameters($data, $hidden)
    {
        foreach ($hidden as $parameter) {
            if (Arr::get($data, $parameter)) {
                Arr::set($data, $parameter, '********');
            }
        }

        return $data;
    }

    /**
     * Extract the session variables from the given request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    private function sessionVariables(Request $request)
    {
        return $request->hasSession() ? $request->session()->all() : [];
    }

    /**
     * Extract the input from the given request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    private function input(Request $request)
    {
        $files = $request->files->all();

        array_walk_recursive($files, function (&$file) {
            $file = [
                'name' => $file->getClientOriginalName(),
                'size' => $file->isFile() ? ($file->getSize() / 1000).'KB' : '0',
            ];
        });

        return array_replace_recursive($request->input(), $files);
    }

    /**
     * Format the given response object.
     *
     * @param  \Symfony\Component\HttpFoundation\Response  $response
     * @return array|string
     */
    protected function response(Response $response)
    {
        $content = $response->getContent();

        if (is_string($content)) {
            if (is_array(json_decode($content, true)) &&
                json_last_error() === JSON_ERROR_NONE) {
                return $this->contentWithinLimits($content)
                        ? $this->hideParameters(json_decode($content, true), ActivityTracker::$hiddenResponseParameters)
                        : 'Purged By Activity Tracker';
            }

            if (Str::startsWith(strtolower($response->headers->get('Content-Type') ?? ''), 'text/plain')) {
                return $this->contentWithinLimits($content) ? $content : 'Purged By Activity Tracker';
            }
        }

        if ($response instanceof RedirectResponse) {
            return 'Redirected to '.$response->getTargetUrl();
        }

        if ($response instanceof IlluminateResponse && $response->getOriginalContent() instanceof View) {
            return [
                'view' => $response->getOriginalContent()->getPath(),
                'data' => $this->extractDataFromView($response->getOriginalContent()),
            ];
        }

        if ($response->getStatusCode() === 500 && $response->exception instanceof \Exception) {
            // return [
            //     'exception' => [
            //         'message' => $response->exception->getMessage(),
            //         'file' => $response->exception->getFile(),
            //         'line' => $response->exception->getLine(),
            //         'previous' => $response->exception->getPrevious(),
            //     ],
            //    'stack' => explode("\n", $response->exception->getTraceAsString()),
            // ];
            return [
                'message' => $response->exception->getMessage(),
                'file' => $response->exception->getFile(),
                'line' => $response->exception->getLine(),
                'previous' => $response->exception->getPrevious(),
            ];
        }

        if (is_string($content) && empty($content)) {
            return 'Empty Response';
        }

        return 'HTML Response';
    }

    /**
     * Determine if the content is within the set limits.
     *
     * @param  string  $content
     * @return bool
     */
    public function contentWithinLimits($content)
    {
        $limit = $this->options['size_limit'] ?? 64;

        return intdiv(mb_strlen($content), 1000) <= $limit;
    }

    /**
     * Extract the data from the given view in array form.
     *
     * @param  \Illuminate\View\View  $view
     * @return array
     */
    protected function extractDataFromView($view)
    {
        return collect($view->getData())->map(function ($value) {
            if ($value instanceof Model) {
                return FormatModel::given($value);
            } elseif (is_object($value)) {
                return [
                    'class' => get_class($value),
                    'properties' => json_decode(json_encode($value), true),
                ];
            } else {
                return json_decode(json_encode($value), true);
            }
        })->toArray();
    }
}
