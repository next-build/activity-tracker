import Analytics from './screens/analytics/Index.vue';
import Visitor from './screens/visitors/Index.vue';
import VisitorPreview from './screens/visitors/Preview.vue';
import VisitorRequestPreview from './screens/visitors/RequestPreview.vue';

export default [
    { path: '/', redirect: '/analytics' },
    {
        path: '/analytics',
        name: 'analytics',
        component: Analytics,
    },
    {
        path: '/visitors',
        name: 'visitors',
        component: Visitor,
    },
    {
        path: '/visitors/:id',
        name: 'visitor-preview',
        component: VisitorPreview,
    },
    {
        path: '/visitors/:visitor_id/requests/:request_id',
        name: 'visitor-request-preview',
        component: VisitorRequestPreview,
    },
];
