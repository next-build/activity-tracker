import { defineComponent, inject, reactive, onBeforeUnmount, toRaw } from 'vue'

import { toType } from './../helpers/util'

//clibboard icon
import { Clippy } from './ToggleIcons/icons'

//theme
import Theme from './../themes/getStyle'

export default defineComponent({
  props: {
    src: {
      type: [Object, String, Number, Boolean, Function],
      default: '',
    },
    rowHovered: Boolean,
    hidden: Boolean,
  },
  setup(props: any) {
    const setting: any = inject('setting')
    const copyConfig: any = toRaw(inject('copyConfig'))

    const state = reactive({ copied: false })

    let copiedTimer: any = null

    onBeforeUnmount(() => {
      if (copiedTimer) {
        clearTimeout(copiedTimer)
        copiedTimer = null
      }
    })

    function handleCopy() {
      const container = document.createElement('textarea')

      container.innerHTML = JSON.stringify(clipboardValue(props.src), null, '  ')

      document.body.appendChild(container)
      container.select()
      document.execCommand('copy')

      document.body.removeChild(container)

      copiedTimer = setTimeout(() => {
        state.copied = false
      }, 5500)

      state.copied = true

      if (typeof setting.enableClipboard === 'function') {
        setting.enableClipboard({
          src: props.src,
          name: copyConfig.name,
          namespace: copyConfig.namespace,
        })
      }
    }

    function getClippyIcon() {
      if (state.copied) {
        return (
          <span>
            <Clippy class="copy-icon" {...Theme(setting.theme, 'copy-icon')} />
            <span {...Theme(setting.theme, 'copy-icon-copied')}>✔</span>
          </span>
        )
      }

      return <Clippy class="copy-icon" {...Theme(setting.theme, 'copy-icon')} />
    }

    function clipboardValue(value: any) {
      const type = toType(value)
      switch (type) {
        case 'function':
        case 'regexp':
          return value.toString()
        default:
          return value
      }
    }

    return () => {
      const style = (Theme(setting.theme, 'copy-to-clipboard') as any).style
      let display = 'inline'

      if (props.hidden) {
        display = 'none'
      }

      return (
        <span
          class="copy-to-clipboard-container"
          title="Copy to clipboard"
          style={{
            verticalAlign: 'top',
            display: props.rowHovered ? 'inline-block' : 'none',
          }}
        >
          <span
            style={{
              ...style,
              display: display,
            }}
            onClick={handleCopy}
          >
            {getClippyIcon()}
          </span>
        </span>
      )
    }
  },
})
