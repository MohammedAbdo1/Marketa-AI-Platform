# MARKETA AI Design System

> A comprehensive, Notion-inspired design system with full RTL support for Arabic

## 📚 Table of Contents

1. [Introduction](#introduction)
2. [Color System](#color-system)
3. [Typography](#typography)
4. [Spacing & Layout](#spacing--layout)
5. [Components](#components)
6. [RTL Support](#rtl-support)
7. [Responsive Design](#responsive-design)
8. [Animations](#animations)
9. [Best Practices](#best-practices)

---

## Introduction

The Marketa AI Design System is a custom CSS framework inspired by Notion's clean, minimal aesthetic. It features:

- **Notion-style colors** - Subtle, professional palette
- **Cairo font** - Beautiful Arabic typography
- **Compact sizes** - 14px base font (Notion-style)
- **Full RTL support** - Automatic layout flipping for Arabic
- **Smooth animations** - Professional, subtle transitions
- **Consistent spacing** - 4px grid system
- **Modular architecture** - Easy to maintain and extend

### File Structure

```
frontend-user/src/design-system/
├── index.css (Main entry point)
├── tokens/
│   ├── colors.css
│   ├── typography.css
│   ├── spacing.css
│   ├── shadows.css
│   ├── radius.css
│   ├── animations.css
│   └── z-index.css
├── components/
│   ├── buttons.css
│   ├── forms.css
│   ├── cards.css
│   ├── modals.css
│   ├── dropdowns.css
│   ├── badges.css
│   └── tooltips.css
├── layouts/
│   ├── containers.css
│   └── rtl.css
└── utilities/
    ├── helpers.css
    └── responsive.css
```

---

## Color System

### Primary Colors

Inspired by Notion's subtle, professional palette:

```css
/* Text Colors */
--color-text-primary: #37352F    /* Main text (Notion's dark gray) */
--color-text-secondary: #787774  /* Muted text */
--color-text-tertiary: #9B9A97   /* Light text */
--color-text-placeholder: #C6C5C2 /* Placeholder text */

/* Background Colors */
--color-bg-primary: #FFFFFF      /* Main background */
--color-bg-secondary: #F7F6F3    /* Secondary background */
--color-bg-tertiary: #ECEBE8     /* Tertiary background */
--color-bg-hover: #F1F0ED        /* Hover state */

/* Brand Color */
--color-brand-primary: #0F7B6C   /* Teal - Primary actions */
```

### Semantic Colors

```css
--color-success: #4D8B31  /* Green */
--color-warning: #CB912F  /* Amber */
--color-error: #E03E3E    /* Red */
--color-info: #337EA9     /* Blue */
--color-purple: #9065B0   /* Purple (Pro/Premium) */
```

### Usage Examples

```html
<!-- Text Colors -->
<p class="text-primary">Main text color</p>
<p class="text-secondary">Secondary text</p>
<p class="text-brand">Brand color text</p>

<!-- Background Colors -->
<div class="bg-primary">White background</div>
<div class="bg-secondary">Light gray background</div>
```

---

## Typography

### Font System

- **Primary Font**: Cairo (Arabic) / System fonts (English)
- **Base Size**: 14px (Notion-style compact)
- **Monospace**: SF Mono / Cascadia Code

```css
--font-primary: 'Cairo', -apple-system, BlinkMacSystemFont, 'Segoe UI', 
                Roboto, 'Helvetica Neue', Arial, sans-serif;

--font-mono: 'SF Mono', 'Cascadia Code', Consolas, monospace;
```

### Font Sizes

```css
--text-xs: 0.6875rem    /* 11px - Very small labels */
--text-sm: 0.8125rem    /* 13px - Small text */
--text-base: 0.875rem   /* 14px - Base (Notion style) */
--text-md: 0.9375rem    /* 15px - Medium */
--text-lg: 1rem         /* 16px - Large */
--text-xl: 1.125rem     /* 18px - Extra large */
--text-2xl: 1.375rem    /* 22px - Heading 3 */
--text-3xl: 1.625rem    /* 26px - Heading 2 */
--text-4xl: 2rem        /* 32px - Heading 1 */
```

### Font Weights

```css
--font-normal: 400     /* Regular text */
--font-medium: 500     /* Medium emphasis */
--font-semibold: 600   /* Strong emphasis */
--font-bold: 700       /* Bold headings */
```

### Usage Examples

```html
<!-- Font Sizes -->
<p class="text-sm">Small text</p>
<p class="text-base">Base text (default)</p>
<p class="text-lg">Large text</p>

<!-- Font Weights -->
<p class="font-medium">Medium weight</p>
<p class="font-semibold">Semibold weight</p>

<!-- Headings -->
<h1 class="text-4xl font-bold">Page Title</h1>
<h2 class="text-3xl font-semibold">Section Title</h2>
<h3 class="text-2xl font-semibold">Subsection</h3>
```

---

## Spacing & Layout

### Spacing Scale (4px base)

```css
--space-0: 0          /* 0px */
--space-1: 0.25rem    /* 4px */
--space-2: 0.5rem     /* 8px */
--space-3: 0.75rem    /* 12px */
--space-4: 1rem       /* 16px */
--space-5: 1.25rem    /* 20px */
--space-6: 1.5rem     /* 24px */
--space-8: 2rem       /* 32px */
--space-10: 2.5rem    /* 40px */
--space-12: 3rem      /* 48px */
```

### Margin & Padding Utilities

```html
<!-- Margin -->
<div class="m-4">Margin all sides 16px</div>
<div class="mt-4">Margin top 16px</div>
<div class="mb-4">Margin bottom 16px</div>
<div class="mx-4">Margin horizontal 16px</div>
<div class="my-4">Margin vertical 16px</div>

<!-- Padding -->
<div class="p-4">Padding all sides 16px</div>
<div class="px-6">Padding horizontal 24px</div>
<div class="py-4">Padding vertical 16px</div>

<!-- Gap (Flexbox/Grid) -->
<div class="d-flex gap-4">
  <div>Item 1</div>
  <div>Item 2</div>
</div>
```

---

## Components

### Buttons

#### Basic Buttons

```html
<button class="btn btn-primary">Primary</button>
<button class="btn btn-secondary">Secondary</button>
<button class="btn btn-ghost">Ghost</button>
<button class="btn btn-outline">Outline</button>
<button class="btn btn-danger">Danger</button>
```

#### Button Sizes

```html
<button class="btn btn-xs btn-primary">Extra Small (24px)</button>
<button class="btn btn-sm btn-primary">Small (28px)</button>
<button class="btn btn-md btn-primary">Medium (32px) - Default</button>
<button class="btn btn-lg btn-primary">Large (40px)</button>
```

#### Icon Buttons

```html
<button class="btn-icon btn-ghost">
  <i class="bx bx-search"></i>
</button>

<button class="btn-icon btn-sm btn-primary">
  <i class="bx bx-plus"></i>
</button>
```

### Forms

#### Input Fields

```html
<div class="form-group">
  <label class="form-label form-label-required">Email</label>
  <input type="email" class="form-control" placeholder="Enter your email">
  <span class="form-hint">We'll never share your email</span>
</div>
```

#### Input Validation

```html
<!-- Error State -->
<input class="form-control is-invalid" value="invalid@">
<span class="invalid-feedback">
  <i class="bx bx-error-circle"></i>
  Please enter a valid email
</span>

<!-- Success State -->
<input class="form-control is-valid" value="valid@example.com">
<span class="valid-feedback">Looks good!</span>
```

### Cards

```html
<div class="card">
  <div class="card-header">
    <h3 class="card-title">Card Title</h3>
    <span class="badge badge-success">Active</span>
  </div>
  <div class="card-body">
    <p class="text-secondary mb-4">
      Card content with description text
    </p>
  </div>
  <div class="card-footer">
    <button class="btn btn-primary">View Details</button>
    <button class="btn btn-ghost">Cancel</button>
  </div>
</div>
```

### Modals

```html
<!-- Backdrop -->
<div class="modal-backdrop" v-if="showModal"></div>

<!-- Modal -->
<div class="modal" v-if="showModal">
  <div class="modal-header">
    <h3 class="modal-title">Confirm Action</h3>
    <button class="modal-close" @click="showModal = false">
      <i class="bx bx-x"></i>
    </button>
  </div>
  <div class="modal-body">
    <p>Are you sure you want to proceed?</p>
  </div>
  <div class="modal-footer">
    <button class="btn btn-secondary" @click="showModal = false">
      Cancel
    </button>
    <button class="btn btn-primary">Confirm</button>
  </div>
</div>
```

### Badges

```html
<span class="badge badge-primary">Primary</span>
<span class="badge badge-success">Success</span>
<span class="badge badge-warning">Warning</span>
<span class="badge badge-error">Error</span>

<!-- With Dot Indicator -->
<span class="badge badge-success badge-dot">Active</span>
```

---

## RTL Support

### Automatic Handling

The design system automatically handles RTL layouts. **No manual work needed!**

```html
<!-- Sidebar automatically positions correctly -->
<aside class="dashboard-sidebar">
  <!-- In Arabic: Right side -->
  <!-- In English: Left side -->
</aside>

<!-- Margin utilities auto-flip -->
<div class="ml-4">
  <!-- In Arabic: becomes mr-4 -->
  <!-- In English: stays ml-4 -->
</div>
```

### Language Detection

RTL is controlled via the `dir` attribute on the HTML element:

```javascript
// Automatically set in App.vue
document.documentElement.setAttribute('dir', locale === 'ar' ? 'rtl' : 'ltr')
```

### Best Practices

1. **Never** use absolute positioning with `left/right` values
2. **Always** use logical properties or utility classes
3. **Test** both English and Arabic views
4. **Use** flexbox/grid for layouts (auto-compatible with RTL)

---

## Responsive Design

### Breakpoints

```css
--breakpoint-sm: 640px   /* Mobile */
--breakpoint-md: 768px   /* Tablet */
--breakpoint-lg: 1024px  /* Desktop */
--breakpoint-xl: 1280px  /* Large Desktop */
```

### Responsive Utilities

```html
<!-- Hide/Show by Device -->
<div class="hide-mobile">Hidden on mobile</div>
<div class="show-mobile">Visible only on mobile</div>
<div class="hide-desktop">Hidden on desktop</div>

<!-- Responsive Flex Direction -->
<div class="d-flex flex-col-mobile">
  <!-- Column on mobile, row on desktop -->
</div>
```

---

## Animations

### Built-in Animations

```html
<!-- Fade In -->
<div class="animate-fade-in">Content fades in</div>

<!-- Slide Up -->
<div class="animate-slide-up">Content slides up</div>

<!-- Scale In -->
<div class="animate-scale-in">Content scales in</div>

<!-- Pulse (continuous) -->
<div class="animate-pulse">Pulsing content</div>
```

### Hover Effects

```html
<!-- Lift Effect -->
<div class="card hover-lift">Lifts on hover</div>

<!-- Scale Effect -->
<button class="btn hover-scale">Scales on hover</button>

<!-- Grow Effect -->
<div class="card hover-grow">Grows slightly on hover</div>
```

### Transitions

```html
<!-- All Properties -->
<div class="transition-all">Smooth transition</div>

<!-- Colors Only -->
<div class="transition-colors">Color transition</div>

<!-- Transform Only -->
<div class="transition-transform">Transform transition</div>
```

---

## Best Practices

### 1. Component Consistency

Always use design system classes instead of custom styles:

❌ **Don't:**
```vue
<button style="padding: 12px; background: teal; color: white;">
  Save
</button>
```

✅ **Do:**
```vue
<button class="btn btn-primary">
  Save
</button>
```

### 2. Spacing Consistency

Use spacing utilities instead of arbitrary values:

❌ **Don't:**
```vue
<div style="margin-top: 23px; padding: 15px;">
```

✅ **Do:**
```vue
<div class="mt-6 p-4">
```

### 3. RTL Awareness

Always think about RTL users:

❌ **Don't:**
```vue
<div style="text-align: left; margin-left: 20px;">
```

✅ **Do:**
```vue
<div class="text-left ml-5">
  <!-- Auto-flips to text-right and mr-5 in RTL -->
</div>
```

### 4. Accessibility

- Use semantic HTML
- Include proper ARIA labels
- Ensure keyboard navigation
- Maintain color contrast

```vue
<!-- Good Accessibility -->
<button class="btn-icon" aria-label="Close dialog">
  <i class="bx bx-x"></i>
</button>

<label class="form-label" for="email">Email Address</label>
<input id="email" class="form-control" type="email">
```

---

## Migration Guide

### Migrating Existing Components

1. **Identify inline styles**
2. **Replace with utility classes**
3. **Test RTL behavior**
4. **Test responsive behavior**

### Example Migration

**Before:**
```vue
<template>
  <div style="padding: 20px; background: white; border-radius: 8px;">
    <h2 style="font-size: 24px; font-weight: 600; margin-bottom: 16px;">
      Title
    </h2>
    <button style="background: teal; color: white; padding: 8px 16px;">
      Save
    </button>
  </div>
</template>
```

**After:**
```vue
<template>
  <div class="card p-5">
    <h2 class="text-2xl font-semibold mb-4">
      Title
    </h2>
    <button class="btn btn-primary">
      Save
    </button>
  </div>
</template>
```

---

## Support

For questions or issues:
1. Check `.cursorrules` for quick reference
2. Review component examples in `/design-system/`
3. Test in both Arabic and English
4. Refer to Notion's UI for inspiration

---

**Last Updated**: November 2025  
**Version**: 1.0.0

