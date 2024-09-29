import { defineComponent, inject, reactive, computed, toRaw, provide } from 'vue'
import { toType } from '../../helpers/util'

import VariableEditor from './../VariableEditor'
import VariableMeta from './../VariableMeta'
import ArrayGroup from './../ArrayGroup'
import ObjectName from './../ObjectName'

//icons
import CollapsedIcon from '../ToggleIcons/CollapsedIcon'
import ExpandedIcon from '../ToggleIcons/ExpandedIcon'

//theme
import Theme from './../../themes/getStyle'

//increment 1 with each nested object & array
const DEPTH_INCREMENT = 1
//single indent is 5px
const SINGLE_INDENT = 5

class JsonVariable {
  name: any
  value: any
  type: string

  constructor(name: any, value: any) {
    this.name = name
    this.value = value
    this.type = toType(value)
  }
}

const JsonObject = defineComponent({
  name: 'Object',
  props: {
    jsvRoot: Boolean,
    src: {
      type: Object,
      required: true,
    },
    name: {
      type: [String, Boolean],
      default: '',
    },
    namespace: {
      type: Array,
      required: true,
    },
    indexOffset: {
      type: Number,
      default: 0,
    },
    type: {
      type: String,
      required: true,
    },
    depth: {
      type: Number,
      required: true,
    },
    parentType: {
      type: String,
      default: '',
    },
  },
  setup(props: any) {
    const setting: any = inject('setting')

    const copyConfig = reactive({
      name: props.name,
      namespace: props.namespace,
    })
    provide('copyConfig', copyConfig)

    const state = reactive<{
      expanded: undefined | Boolean
      hovered: Boolean
    }>({
      expanded: undefined,
      hovered: false,
    })
    const size = computed(() => {
      return Object.keys(props.src).length
    })
    const objectType = computed(() => {
      return props.type === 'array' ? 'array' : 'object'
    })
    const expanded = computed({
      get: () => {
        if (state.expanded !== undefined) {
          return state.expanded
        }

        return (
          (setting.collapsed === false || (setting.collapsed !== true && setting.collapsed > props.depth)) &&
          (!props.shouldCollapse ||
            props.shouldCollapse({
              name: props.name,
              src: props.src,
              type: toType(props.src),
              namespace: props.namespace,
            }) === false) &&
          size.value !== 0 //initialize closed if object has no items
        )
      },
      set: (val) => {
        state.expanded = val
      },
    })

    function toggleCollapsed() {
      expanded.value = !expanded.value
    }
    function getObjectContent(_: any, src: any, contentProps: any) {
      return (
        <div class="pushed-content object-container">
          <div class="object-content" {...Theme(setting.theme, 'pushed-content')}>
            {renderObjectContents(src, contentProps)}
          </div>
        </div>
      )
    }
    function getEllipsis() {
      if (size.value === 0) {
        //don't render an ellipsis when an object has no items
        return null
      } else {
        return (
          <div {...Theme(setting.theme, 'ellipsis')} class="node-ellipsis" onClick={toggleCollapsed}>
            ...
          </div>
        )
      }
    }
    function getObjectMetaData(_: any) {
      return <VariableMeta rowHovered={state.hovered} size={size.value} {...toRaw(props)} />
    }
    function getBraceStart() {
      const { src, parentType } = props
      const { theme, iconStyle } = setting

      if (parentType === 'array_group') {
        return (
          <span>
            <span {...Theme(theme, 'brace')}>{objectType.value === 'array' ? '[' : '{'}</span>
            {expanded.value ? getObjectMetaData(src) : null}
          </span>
        )
      }

      const IconComponent = expanded.value ? ExpandedIcon : CollapsedIcon

      return (
        <span>
          <span onClick={toggleCollapsed} {...Theme(theme, 'brace-row')}>
            <div class="icon-container" {...Theme(theme, 'icon-container')}>
              <IconComponent {...{ theme, iconStyle }} />
            </div>
            <ObjectName theme={theme} quotesOnKeys={setting.quotesOnKeys} {...props} />
            <span {...Theme(theme, 'brace')}>{objectType.value === 'array' ? '[' : '{'}</span>
          </span>
          {expanded.value ? getObjectMetaData(src) : null}
        </span>
      )
    }
    function renderObjectContents(variables: any, contentProps: any) {
      const elements: any[] = []
      let variable
      let keys = Object.keys(variables || {})
      if (setting.sortKeys && objectType.value !== 'array') {
        keys = keys.sort()
      }

      keys.forEach((name) => {
        variable = new JsonVariable(name, variables[name])

        if (props.parentType === 'array_group' && props.indexOffset) {
          variable.name = parseInt(variable.name) + props.indexOffset
        }
        if (!variables.hasOwnProperty(name)) {
          return
        } else if (variable.type === 'object') {
          elements.push(
            <JsonObject
              {...contentProps}
              key={variable.name}
              depth={props.depth + DEPTH_INCREMENT}
              name={variable.name}
              src={variable.value}
              namespace={props.namespace.concat(variable.name)}
              parentType={objectType.value}
              type="object"
            />,
          )
        } else if (variable.type === 'array') {
          let ObjectComponent = JsonObject

          if (setting.groupArraysAfterLength && variable.value.length > setting.groupArraysAfterLength) {
            ObjectComponent = ArrayGroup
          }

          elements.push(
            <ObjectComponent
              {...contentProps}
              key={variable.name}
              depth={props.depth + DEPTH_INCREMENT}
              name={variable.name}
              src={variable.value}
              namespace={props.namespace.concat(variable.name)}
              type="array"
              parentType={objectType.value}
            />,
          )
        } else {
          elements.push(
            <VariableEditor
              {...contentProps}
              key={variable.name + '_' + props.namespace}
              variable={variable}
              singleIndent={SINGLE_INDENT}
              type={props.type}
            />,
          )
        }
      })

      return elements
    }

    return () => {
      const styles: any = {}
      if (!props.jsvRoot && props.parentType !== 'array_group') {
        styles.paddingLeft = setting.indentWidth * SINGLE_INDENT
      } else if (props.parentType === 'array_group') {
        styles.borderLeft = 0
        styles.display = 'inline'
      }

      const { depth, src, namespace, name, type, parentType, jsvRoot, ...rest } = props

      return (
        <div
          class="object-key-val"
          onMouseenter={() => (state.hovered = true)}
          onMouseleave={() => (state.hovered = false)}
          {...Theme(setting.theme, props.jsvRoot ? 'jsv-root' : 'objectKeyVal', styles)}
        >
          {getBraceStart()}
          {expanded.value ? getObjectContent(depth, src, rest) : getEllipsis()}
          <span class="brace-row">
            <span
              style={{
                ...(Theme(setting.theme, 'brace') as any).style,
                paddingLeft: expanded.value ? '3px' : '0px',
              }}
            >
              {objectType.value === 'array' ? ']' : '}'}
            </span>
            {expanded.value ? null : getObjectMetaData(src)}
          </span>
        </div>
      )
    }
  },
})

export default JsonObject
