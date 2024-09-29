import { defineComponent, inject, toRaw } from 'vue'

import CopyToClipboard from './CopyToClipboard'

//theme
import Theme from './../themes/getStyle'

export default defineComponent({
  props: {
    src: {
      type: Object,
      required: true,
    },
    size: {
      type: Number,
      required: true,
    },
    rowHovered: Boolean,
  },
  setup(props) {
    const setting: any = inject('setting')

    function getObjectSize() {
      if (setting.displayObjectSize) {
        return (
          <span class="object-size" {...Theme(setting.theme, 'object-size')}>
            {props.size} item{props.size === 1 ? '' : 's'}
          </span>
        )
      }
    }

    return () => (
      <div
        {...Theme(setting.theme, 'object-meta-data')}
        class="object-meta-data"
        onClick={(e) => {
          e.stopPropagation()
        }}
      >
        {/* size badge display */}
        {getObjectSize()}
        {/* copy to clipboard icon */}
        {setting.enableClipboard ? <CopyToClipboard src={toRaw(props.src)} rowHovered={props.rowHovered} /> : null}
      </div>
    )
  },
})
