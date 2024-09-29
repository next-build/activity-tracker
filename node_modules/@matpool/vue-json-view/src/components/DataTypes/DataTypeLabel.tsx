import { defineComponent, inject } from 'vue'

//theme
import Theme from './../../themes/getStyle'

export default defineComponent({
  props: {
    typeName: {
      type: String,
      required: true,
    },
  },
  setup(props) {
    const setting: any = inject('setting')

    return () => (
      <span class="data-type-label" {...Theme(setting.theme, 'data-type-label')}>
        {props.typeName}
      </span>
    )
  },
})
