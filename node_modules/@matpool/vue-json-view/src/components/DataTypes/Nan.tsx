import { defineComponent, inject } from 'vue'

//theme
import Theme from '../../themes/getStyle'

export default defineComponent({
  setup() {
    const setting: any = inject('setting')

    return () => <div {...Theme(setting.theme, 'nan')}>NaN</div>
  },
})
