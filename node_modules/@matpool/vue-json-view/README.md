# vue-json-view
JSON viewer component, for Vue.js 3.

This project is forked from [react-json-view](https://github.com/mac-s-g/react-json-view) and is the vue3 version of that.

**Show only, can’t modify.**


### Install

`npm install --save @matpool/vue-json-view`


### Usage
```js
// import the vue-json-view component
import VueJsonView from '@matpool/vue-json-view'

// use the component in your app!
<VueJsonView src={my_json_object} />
```

### Props
Name|Type|Default|Description
|:---|:---|:---|:---
`src`|`JSON Object`|None|This property contains your input JSON
`name`|`string` or `false`|false|Contains the name of your root node.  Use `null` or `false` for no name.
`theme`|`string`|"monokai"|RJV supports base-16 themes.  Check out the list of supported themes [in the demo](https://mac-s-g.github.io/react-json-view/demo/dist/). A custom "monokai" theme applies by default.
`collapsed`|`boolean` or `integer`|`false`|When set to `true`, all nodes will be collapsed by default.  Use an integer value to collapse at a particular depth.
`sortKeys`|`boolean`|`false`|set to true to sort object keys