import { defineComponent, inject, provide, reactive, toRaw } from 'vue'
import Theme from './../themes/getStyle'

import VariableMeta from './VariableMeta'
import ObjectName from './ObjectName'
import ObjectComponent from './DataTypes/Object'

//icons
import CollapsedIcon from './ToggleIcons/CollapsedIcon'
import ExpandedIcon from './ToggleIcons/ExpandedIcon'

//single indent is 5px
const SINGLE_INDENT = 5

export default defineComponent({
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
      namespace: toRaw(props.namespace),
    })
    provide('copyConfig', copyConfig)

    const state = reactive<{ [key: string]: any[] }>({
      expanded: [],
    })

    function toggleCollapsed(i: number) {
      const newExpanded: any[] = []
      for (const j in state.expanded) {
        newExpanded.push(state.expanded[j])
      }
      newExpanded[i] = !newExpanded[i]
      state.expanded = newExpanded
    }

    function getExpandedIcon(i: number) {
      const { theme, iconStyle } = setting

      if (state.expanded[i]) {
        return <ExpandedIcon {...{ theme, iconStyle }} />
      }

      return <CollapsedIcon {...{ theme, iconStyle }} />
    }

    return () => {
      const { groupArraysAfterLength, theme } = setting
      const { src, depth, name, jsvRoot, namespace, parentType, ...rest } = toRaw(props)

      let object_padding_left = 0

      const array_group_padding_left = setting.indentWidth * SINGLE_INDENT

      if (!jsvRoot) {
        object_padding_left = setting.indentWidth * SINGLE_INDENT
      }

      const size = groupArraysAfterLength
      const groups = Math.ceil(src.length / size)

      return (
        <div
          class="object-key-val"
          {...Theme(theme, jsvRoot ? 'jsv-root' : 'objectKeyVal', {
            paddingLeft: object_padding_left,
          })}
        >
          <ObjectName theme={theme} quotesOnKeys={setting.quotesOnKeys} {...props} />

          <span>
            <VariableMeta size={src.length} {...props} />
          </span>
          {[...Array(groups)].map((_, i) => (
            <div
              key={i}
              class="object-key-val array-group"
              {...Theme(theme, 'objectKeyVal', {
                marginLeft: 6,
                paddingLeft: array_group_padding_left,
              })}
            >
              <span {...Theme(theme, 'brace-row')}>
                <div
                  class="icon-container"
                  {...Theme(theme, 'icon-container')}
                  onClick={() => {
                    toggleCollapsed(i)
                  }}
                >
                  {getExpandedIcon(i)}
                </div>
                {state.expanded[i] ? (
                  <ObjectComponent
                    {...rest}
                    key={name + i}
                    depth={0}
                    name={false}
                    collapsed={false}
                    indexOffset={i * size}
                    src={src.slice(i * size, i * size + size)}
                    namespace={namespace}
                    type="array"
                    parentType="array_group"
                  />
                ) : (
                  <span
                    {...Theme(theme, 'brace')}
                    onClick={() => {
                      toggleCollapsed(i)
                    }}
                    class="array-group-brace"
                  >
                    [
                    <div {...Theme(theme, 'array-group-meta-data')} class="array-group-meta-data">
                      <span class="object-size" {...Theme(theme, 'object-size')}>
                        {i * size}
                        {' - '}
                        {i * size + size > src.length ? src.length - 1 : i * size + size - 1}
                      </span>
                    </div>
                    ]
                  </span>
                )}
              </span>
            </div>
          ))}
        </div>
      )
    }
  },
})
