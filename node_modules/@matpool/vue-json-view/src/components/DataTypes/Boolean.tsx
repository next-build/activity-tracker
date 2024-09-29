import { defineComponent, inject } from 'vue'
import DataTypeLabel from './DataTypeLabel'

//theme
import Theme from './../../themes/getStyle'

export default defineComponent({
  props: {
    value: Boolean,
  },
  setup(props) {
    const setting: any = inject('setting')

    return () => (
      <div {...Theme(setting.theme, 'boolean')}>
        {setting.displayDataTypes && <DataTypeLabel typeName="bool" />}
        {props.value ? 'true' : 'false'}
      </div>
    )
  },
})
