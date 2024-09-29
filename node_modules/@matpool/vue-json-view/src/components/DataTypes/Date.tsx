import { defineComponent, inject } from 'vue'
import DataTypeLabel from './DataTypeLabel'

//theme
import Theme from '../../themes/getStyle'

export default defineComponent({
  props: {
    value: {
      type: Date,
      required: true,
    },
  },
  setup(props) {
    const setting: any = inject('setting')

    const display_options = {
      weekday: 'short',
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    }

    return () => (
      <div {...Theme(setting.theme, 'date')}>
        {setting.displayDataTypes && <DataTypeLabel typeName="date" />}
        <span class="date-value" {...Theme(setting.theme, 'date-value')}>
          {props.value.toLocaleTimeString('en-us', display_options as Intl.DateTimeFormatOptions)}
        </span>
      </div>
    )
  },
})
