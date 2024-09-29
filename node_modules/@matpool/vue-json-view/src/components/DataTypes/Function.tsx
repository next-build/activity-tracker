import { defineComponent, inject, reactive } from 'vue'
import DataTypeLabel from './DataTypeLabel'

//theme
import Theme from '../../themes/getStyle'

export default defineComponent({
  props: {
    value: {
      type: Function,
      required: true,
    },
  },
  setup(props) {
    const setting: any = inject('setting')

    const state = reactive({
      collapsed: true,
    })
    const toggleCollapsed = () => {
      state.collapsed = !state.collapsed
    }

    const getFunctionDisplay = (collapsed: boolean) => {
      if (collapsed) {
        return (
          <span>
            {props.value.toString().replace(/\{[\s\S]+/, '')}
            <span class="function-collapsed" style={{ fontWeight: 'bold' }}>
              <span>{'{'}</span>
              <span {...Theme(setting.theme, 'ellipsis')}>...</span>
              <span>{'}'}</span>
            </span>
          </span>
        )
      } else {
        return props.value.toString()
      }
    }

    return () => {
      const { displayDataTypes, theme } = setting

      return (
        <div {...Theme(theme, 'function')}>
          {displayDataTypes && <DataTypeLabel typeName="function" />}
          <span {...Theme(theme, 'function-value')} class="rjv-function-container" onClick={toggleCollapsed}>
            {getFunctionDisplay(state.collapsed)}
          </span>
        </div>
      )
    }
  },
})
