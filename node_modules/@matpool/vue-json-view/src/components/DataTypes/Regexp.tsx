import { defineComponent, inject } from 'vue'
import DataTypeLabel from './DataTypeLabel'

//theme
import Theme from '../../themes/getStyle'

export default defineComponent({
  props: {
    value: {
      type: RegExp,
      required: true,
    },
  },
  setup(props) {
    const setting: any = inject('setting')

    return () => (
      <div {...Theme(setting.theme, 'regexp')}>
        {setting.displayDataTypes && <DataTypeLabel typeName="regexp" />}
        {props.value.toString()}
      </div>
    )
  },
})
