import { defineComponent, inject } from 'vue'

import Theme from '../../themes/getStyle'

import { CircleMinus, SquareMinus, ArrowDown } from './icons'

export default defineComponent({
  setup() {
    const setting: any = inject('setting')

    return () => {
      switch (setting.iconStyle) {
        case 'triangle':
          return <ArrowDown {...Theme(setting.theme, 'expanded-icon')} class="expanded-icon" />
        case 'square':
          return <SquareMinus {...Theme(setting.theme, 'expanded-icon')} class="expanded-icon" />
        default:
          return <CircleMinus {...Theme(setting.theme, 'expanded-icon')} class="expanded-icon" />
      }
    }
  },
})
