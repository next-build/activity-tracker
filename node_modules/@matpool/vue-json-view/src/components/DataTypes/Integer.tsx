import { defineComponent, inject } from 'vue'
import DataTypeLabel from './DataTypeLabel'

//theme
import Theme from '../../themes/getStyle'

export default defineComponent({
  props: {
    value: {
      type: Number,
      required: true,
    },
  },
  setup(props) {
    const setting: any = inject('setting')

    return () => (
      <div {...Theme(setting.theme, 'integer')}>
        {setting.displayDataTypes && <DataTypeLabel typeName="int" />}
        {props.value}
      </div>
    )
  },
})
