// Keyboard Shortcuts for Editor (Canva-like)

export const setupKeyboardShortcuts = (handlers) => {
  const handleKeyDown = (e) => {
    const ctrl = e.ctrlKey || e.metaKey
    const shift = e.shiftKey
    const alt = e.altKey

    // File Operations
    if (ctrl && e.key === 's') {
      e.preventDefault()
      handlers.save?.()
      return
    }

    if (ctrl && e.key === 'e') {
      e.preventDefault()
      handlers.export?.()
      return
    }

    // Edit Operations
    if (ctrl && e.key === 'z' && !shift) {
      e.preventDefault()
      handlers.undo?.()
      return
    }

    if (ctrl && (e.key === 'y' || (e.key === 'z' && shift))) {
      e.preventDefault()
      handlers.redo?.()
      return
    }

    if (ctrl && e.key === 'x') {
      e.preventDefault()
      handlers.cut?.()
      return
    }

    if (ctrl && e.key === 'c') {
      e.preventDefault()
      handlers.copy?.()
      return
    }

    if (ctrl && e.key === 'v') {
      e.preventDefault()
      handlers.paste?.()
      return
    }

    if (ctrl && e.key === 'a') {
      e.preventDefault()
      handlers.selectAll?.()
      return
    }

    if (ctrl && e.key === 'd') {
      e.preventDefault()
      handlers.duplicate?.()
      return
    }

    // Object Operations
    if (e.key === 'Delete' || e.key === 'Backspace') {
      e.preventDefault()
      handlers.delete?.()
      return
    }

    // Zoom
    if (ctrl && e.key === '+') {
      e.preventDefault()
      handlers.zoomIn?.()
      return
    }

    if (ctrl && e.key === '-') {
      e.preventDefault()
      handlers.zoomOut?.()
      return
    }

    if (ctrl && e.key === '0') {
      e.preventDefault()
      handlers.zoomReset?.()
      return
    }

    // Toggle Panels
    if (ctrl && e.key === 'k') {
      e.preventDefault()
      handlers.toggleSearch?.()
      return
    }

    // Layers
    if (ctrl && e.key === ']') {
      e.preventDefault()
      handlers.bringForward?.()
      return
    }

    if (ctrl && e.key === '[') {
      e.preventDefault()
      handlers.sendBackward?.()
      return
    }

    if (ctrl && shift && e.key === ']') {
      e.preventDefault()
      handlers.bringToFront?.()
      return
    }

    if (ctrl && shift && e.key === '[') {
      e.preventDefault()
      handlers.sendToBack?.()
      return
    }

    // Lock/Unlock
    if (ctrl && e.key === 'l') {
      e.preventDefault()
      handlers.toggleLock?.()
      return
    }

    // Group/Ungroup
    if (ctrl && e.key === 'g') {
      e.preventDefault()
      handlers.group?.()
      return
    }

    if (ctrl && shift && e.key === 'g') {
      e.preventDefault()
      handlers.ungroup?.()
      return
    }
  }

  // Add event listener
  document.addEventListener('keydown', handleKeyDown)

  // Return cleanup function
  return () => {
    document.removeEventListener('keydown', handleKeyDown)
  }
}

// Shortcuts reference
export const SHORTCUTS_REFERENCE = {
  file: [
    { keys: 'Ctrl + S', action: 'حفظ' },
    { keys: 'Ctrl + E', action: 'تصدير' }
  ],
  edit: [
    { keys: 'Ctrl + Z', action: 'تراجع' },
    { keys: 'Ctrl + Y', action: 'إعادة' },
    { keys: 'Ctrl + X', action: 'قص' },
    { keys: 'Ctrl + C', action: 'نسخ' },
    { keys: 'Ctrl + V', action: 'لصق' },
    { keys: 'Ctrl + A', action: 'تحديد الكل' },
    { keys: 'Ctrl + D', action: 'تكرار' }
  ],
  object: [
    { keys: 'Delete', action: 'حذف' },
    { keys: 'Ctrl + L', action: 'قفل/فك القفل' },
    { keys: 'Ctrl + G', action: 'تجميع' },
    { keys: 'Ctrl + Shift + G', action: 'فك التجميع' }
  ],
  layers: [
    { keys: 'Ctrl + ]', action: 'تقديم للأمام' },
    { keys: 'Ctrl + [', action: 'إرسال للخلف' },
    { keys: 'Ctrl + Shift + ]', action: 'تقديم للأمام تماماً' },
    { keys: 'Ctrl + Shift + [', action: 'إرسال للخلف تماماً' }
  ],
  zoom: [
    { keys: 'Ctrl + +', action: 'تكبير' },
    { keys: 'Ctrl + -', action: 'تصغير' },
    { keys: 'Ctrl + 0', action: 'إعادة تعيين الزوم' }
  ]
}

