import Theme from './../themes/getStyle'

export default function getObjectName(props: any) {
  const { parentType, quotesOnKeys, theme, jsvRoot, name, displayArrayKey } = props

  const display_name = props.name ? props.name : ''

  if (jsvRoot && (name === false || name === null)) {
    return <span />
  } else if (parentType == 'array') {
    return displayArrayKey ? (
      <span {...Theme(theme, 'array-key')}>
        <span class="array-key">{display_name}</span>
        <span {...Theme(theme, 'colon')}>:</span>
      </span>
    ) : (
      <span />
    )
  } else {
    return (
      <span {...Theme(theme, 'object-name')}>
        <span class="object-key">
          {quotesOnKeys && <span style={{ verticalAlign: 'top' }}>"</span>}
          <span>{display_name}</span>
          {quotesOnKeys && <span style={{ verticalAlign: 'top' }}>"</span>}
        </span>
        <span {...Theme(theme, 'colon')}>:</span>
      </span>
    )
  }
}
