import { defineComponent, inject } from 'vue'

import Theme from '../../themes/getStyle'

import { CirclePlus, SquarePlus, ArrowRight } from './icons'

export default defineComponent({
  setup() {
    const setting: any = inject('setting')

    return () => {
      switch (setting.iconStyle) {
        case 'triangle':
          return <ArrowRight {...Theme(setting.theme, 'collapsed-icon')} class="collapsed-icon" />
        case 'square':
          return <SquarePlus {...Theme(setting.theme, 'collapsed-icon')} class="collapsed-icon" />
        default:
          return <CirclePlus {...Theme(setting.theme, 'collapsed-icon')} class="collapsed-icon" />
      }
    }
  },
})
