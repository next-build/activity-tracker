import { computed, defineComponent, provide, reactive, toRaw } from 'vue'

import JsonObject from './components/DataTypes/Object'
import ArrayGroup from './components/ArrayGroup'
import { JsonNull, JsonUndefined } from './components/DataTypes/DataTypes'
import { toType } from './helpers/util'

//global theme
import Theme from './themes/getStyle'

class ParseJSONError {
  constructor(public message: string) {}
}

export default defineComponent({
  name: 'VueJsonView',
  props: {
    theme: {
      type: String,
      default: '',
    },
    src: {
      type: [String, Object],
      required: true,
    },
    collapsed: {
      type: [Boolean, Number],
      default: false,
    },
    sortKeys: {
      type: Boolean,
      default: false,
    },
    name: {
      type: [Boolean, String],
      default: false,
    },
    enableClipboard: {
      type: [Boolean, Function],
      default: true,
    },
  },
  setup(props) {
    // initialize
    const setting = reactive({
      theme: 'monokai',
      collapseStringsAfterLength: false,
      shouldCollapse: false,
      quotesOnKeys: true,
      groupArraysAfterLength: 100,
      indentWidth: 2,
      enableClipboard: props.enableClipboard,
      displayObjectSize: true,
      displayDataTypes: false,
      iconStyle: 'triangle',
      defaultValue: null,
      displayArrayKey: true,
      collapsed: false,
      sortKeys: true,
    })

    provide('setting', setting)

    const srcRef = computed(() => {
      try {
        if (typeof props.src === 'object') {
          return props.src
        } else {
          return JSON.parse(props.src)
        }
      } catch (error: any) {
        return new ParseJSONError(error.message || 'src必须是一个JSON数据')
      }
    })

    const name = computed(() => {
      if (srcRef.value instanceof ParseJSONError) {
        return 'ERROR'
      }
      return props.name
    })

    return () => {
      const propsRaw: any = { ...toRaw(props) }
      delete propsRaw.src
      if (!propsRaw.theme) {
        delete propsRaw.theme
      }
      const objectProps = {
        ...propsRaw,
        src: srcRef.value,
        name: name.value,
        namespace: [name.value],
        depth: 0,
        jsvRoot: true,
        type: toType(srcRef.value),
      }

      Object.assign(setting, propsRaw)

      function getObjectComponent() {
        if (srcRef.value === null) {
          return <JsonNull />
        }
        if (srcRef.value === undefined) {
          return <JsonUndefined />
        }

        let ObjectComponent = JsonObject
        if (
          Array.isArray(srcRef.value) &&
          setting.groupArraysAfterLength &&
          srcRef.value.length > setting.groupArraysAfterLength
        ) {
          ObjectComponent = ArrayGroup
        }
        return <ObjectComponent {...objectProps} />
      }

      return (
        <div class="vue-json-view" {...Theme(setting.theme, 'app-container')}>
          <div class="pretty-json-container object-container">
            <div class="object-content">{getObjectComponent()}</div>
          </div>
        </div>
      )
    }
  },
})
