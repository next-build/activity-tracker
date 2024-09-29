import { defineComponent, inject, reactive } from 'vue'

import CopyToClipboard from './CopyToClipboard'

//data type components
import {
  JsonBoolean,
  JsonDate,
  JsonFloat,
  JsonFunction,
  JsonInteger,
  JsonNan,
  JsonNull,
  JsonRegexp,
  JsonString,
  JsonUndefined,
} from './DataTypes/DataTypes'

//theme
import Theme from './../themes/getStyle'

export default defineComponent({
  props: {
    variable: {
      type: Object,
      required: true,
    },
    singleIndent: {
      type: Number,
      required: true,
    },
    type: {
      type: String,
      required: true,
    },
  },
  setup(props) {
    const setting: any = inject('setting')

    const state = reactive({
      editMode: false,
      editValue: '',
      hovered: false,
      renameKey: false,
      parsedInput: {
        type: false,
        value: null,
      },
    })

    const getValue = (variable: any, editMode: any) => {
      const type = editMode ? false : variable.type
      const valueProps = {
        value: variable.value,
        theme: setting.theme,
        displayDataTypes: setting.displayDataTypes,
      }

      switch (type) {
        case false:
          return null
        case 'string':
          return <JsonString {...valueProps} />
        case 'integer':
          return <JsonInteger {...valueProps} />
        case 'float':
          return <JsonFloat {...valueProps} />
        case 'boolean':
          return <JsonBoolean {...valueProps} />
        case 'function':
          return <JsonFunction {...valueProps} />
        case 'null':
          return <JsonNull {...valueProps} />
        case 'nan':
          return <JsonNan {...valueProps} />
        case 'undefined':
          return <JsonUndefined {...valueProps} />
        case 'date':
          return <JsonDate {...valueProps} />
        case 'regexp':
          return <JsonRegexp {...valueProps} />
        default:
          // catch-all for types that weren't anticipated
          return <div class="object-value">{JSON.stringify(variable.value)}</div>
      }
    }

    return () => {
      return (
        <div
          {...Theme(setting.theme, 'objectKeyVal', {
            paddingLeft: (setting.indentWidth as number) * (props.singleIndent as number),
          })}
          onMouseenter={() => (state.hovered = true)}
          onMouseleave={() => (state.hovered = false)}
          class="variable-row"
          key={props.variable.name}
        >
          {props.type == 'array' ? (
            setting.displayArrayKey ? (
              <span {...Theme(setting.theme, 'array-key')}>
                {props.variable.name}
                <div {...Theme(setting.theme, 'colon')}>:</div>
              </span>
            ) : null
          ) : (
            <span>
              <span {...Theme(setting.theme, 'object-name')} class="object-key">
                {!!setting.quotesOnKeys && <span style={{ verticalAlign: 'top' }}>"</span>}
                <span style={{ display: 'inline-block' }}>{props.variable.name}</span>
                {!!setting.quotesOnKeys && <span style={{ verticalAlign: 'top' }}>"</span>}
              </span>
              <span {...Theme(setting.theme, 'colon')}>:</span>
            </span>
          )}
          <div
            class="variable-value"
            {...Theme(setting.theme, 'variableValue', {
              cursor: 'default',
            })}
          >
            {getValue(props.variable, state.editMode)}
          </div>
          {setting.enableClipboard ? (
            <CopyToClipboard rowHovered={state.hovered} hidden={state.editMode} src={props.variable.value} />
          ) : null}
        </div>
      )
    }
  },
})
