# Theme Refactor Guide

## Overview

This document describes the comprehensive theme system refactor completed for the admin dashboard. The refactor centralizes all theming using CSS variables, implements proper light/dark mode support, and ensures consistent styling across all components.

---

## Key Changes

### 1. **Centralized CSS Variables** 

All colors, spacing, typography, and shadows are now centralized in `:root` for light mode, with dark mode overrides in `html.dark` selector.

#### Before:
```css
:root {
    --green: #03624C;
    --accent: #0fb9b1;
    /* ... scattered variables ... */
}

html.dark {
    --surface: #1a1a1a;
    /* Spacing/typography were ONLY in dark mode! */
    --spacing-lg: 16px;
    --font-size-base: 14px;
}
```

#### After:
```css
:root {
    /* ===== LIGHT MODE COLORS ===== */
    --color-primary: #03624C;
    --color-accent: #0fb9b1;
    --color-surface: #ffffff;
    --color-text: #18242b;
    /* ... all spacing, typography, shadows ... */
    
    /* ===== SPACING (available in both modes) ===== */
    --spacing-xs: 4px;
    --spacing-lg: 16px;
    /* ... */
}

html.dark {
    /* ===== DARK MODE COLOR OVERRIDES ===== */
    --color-surface: #1a1a1a;
    --color-text: #e8ecef;
    --shadow-sm: 0 8px 24px rgba(0, 0, 0, 0.3);
}
```

### 2. **New Variable Naming Convention**

All variables now use a consistent `--color-*` naming scheme:

| Old | New | Usage |
|-----|-----|-------|
| `--accent` | `--color-accent` | Primary accent/interactive elements |
| `--surface` | `--color-surface` | Card backgrounds, containers |
| `--text` | `--color-text` | Primary text |
| `--muted` | `--color-text-secondary` | Secondary text, muted labels |
| `--bg` | `--color-bg` | Page background |
| `--green` | `--color-primary` | Brand primary color |

### 3. **Fixed Layout Breaking Issues**

- **Spacing preserved**: All `--spacing-*` variables now defined in `:root` (not just in dark mode)
- **Consistent padding**: `.settings-row`, `.custom-card`, all components now maintain consistent spacing in both modes
- **No more alignment shifts**: Borders, shadows, and backgrounds now use variables consistently

### 4. **Proper Dark Mode Support**

All components now properly adapt to dark mode:

```css
.top-header {
    background: var(--color-surface);  /* Uses light surface in light mode, dark in dark mode */
    border-bottom: 1px solid var(--color-border);
    box-shadow: var(--shadow-sm);
}
```

### 5. **Fixed Accent Color Handling**

#### Before (Broken):
```javascript
document.documentElement.style.setProperty('--accent-soft', color + '22');  // ❌ Doesn't work! '#0fb9b122' is invalid
```

#### After (Correct):
```javascript
const rgb = hexToRgb(color);  // Convert #0fb9b1 to {r: 15, g: 185, b: 177}
document.documentElement.style.setProperty(
    '--color-accent-soft', 
    `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, 0.14)`  // ✅ Proper RGBA
);
```

### 6. **Improved Component Styling**

#### Cards:
```css
.custom-card {
    background: var(--color-surface);
    border: 1px solid var(--color-border-strong);
    color: var(--color-text);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.custom-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}
```

#### Settings Row:
```css
.settings-row {
    background: var(--color-surface-soft);
    border: 1px solid var(--color-border-strong);
    color: var(--color-text);
    transition: var(--transition-base);
    padding: 18px 20px;  /* Consistent spacing preserved */
}

.settings-row:hover {
    background: var(--color-surface);
}
```

#### Form Elements:
```css
.form-control {
    background: var(--color-surface-strong);
    border: 1px solid var(--color-border-strong);
    color: var(--color-text);
}

.form-control:focus {
    border-color: var(--color-accent);
    box-shadow: 0 0 0 3px var(--color-accent-soft);
}

.form-control::placeholder {
    color: var(--color-text-secondary);
}
```

---

## CSS Variable Reference

### Colors (Light Mode - Defined in `:root`)

```css
--color-primary: #03624C;           /* Brand primary */
--color-accent: #0fb9b1;            /* Interactive elements */
--color-accent-soft: rgba(..., 0.14);
--color-accent-dark: rgba(..., 0.2);

--color-surface: #ffffff;           /* Card/container background */
--color-surface-soft: #f7fbf9;      /* Subtle surfaces */
--color-surface-strong: #edf7f2;    /* Input backgrounds */

--color-text: #18242b;              /* Primary text */
--color-text-secondary: #6c7884;    /* Secondary text, labels */

--color-bg: #f4fbfa;                /* Page background */
--color-bg-soft: #eaf6f3;

--color-border: rgba(22, 40, 56, 0.08);
--color-border-strong: rgba(22, 40, 56, 0.12);
```

### Colors (Dark Mode - Defined in `html.dark`)

```css
html.dark {
    --color-surface: #1a1a1a;
    --color-surface-soft: #242424;
    --color-surface-strong: #2d2d2d;
    
    --color-text: #e8ecef;
    --color-text-secondary: #a0a8b0;
    
    --color-bg: #0f0f0f;
    --color-bg-soft: #1a1a1a;
    
    --color-border: rgba(255, 255, 255, 0.08);
    --color-border-strong: rgba(255, 255, 255, 0.12);
    
    /* Shadows are darker in dark mode */
    --shadow-sm: 0 8px 24px rgba(0, 0, 0, 0.3);
    --shadow-md: 0 12px 32px rgba(0, 0, 0, 0.4);
    --shadow-lg: 0 22px 55px rgba(0, 0, 0, 0.5);
}
```

### Spacing (Both Modes)

```css
--spacing-xs: 4px;
--spacing-sm: 8px;
--spacing-md: 12px;
--spacing-lg: 16px;
--spacing-xl: 24px;
--spacing-2xl: 32px;
```

### Typography (Both Modes)

```css
--font-family: 'Instrument Sans', system-ui, -apple-system, sans-serif;
--font-size-xs: 11px;
--font-size-sm: 12px;
--font-size-base: 14px;
--font-size-lg: 16px;
--font-size-xl: 18px;
--font-size-2xl: 24px;
--font-size-3xl: 32px;

--font-weight-regular: 400;
--font-weight-medium: 500;
--font-weight-semibold: 600;
--font-weight-bold: 700;
--font-weight-extrabold: 800;
```

### Shadows & Effects (Light Mode)

```css
--shadow-sm: 0 8px 24px rgba(0, 0, 0, 0.05);
--shadow-md: 0 12px 32px rgba(0, 0, 0, 0.08);
--shadow-lg: 0 22px 55px rgba(0, 0, 0, 0.12);
--shadow-hover: 0 16px 40px rgba(15, 185, 177, 0.2);
```

### Transitions (Both Modes)

```css
--transition-fast: all 0.15s ease;
--transition-base: all 0.25s ease;
--transition-slow: all 0.35s ease;
```

### Layout (Both Modes)

```css
--sidebar-width: 250px;
--sidebar-width-collapsed: 82px;
--header-height: 64px;
--radius-sm: 6px;
--radius-md: 14px;
--radius-lg: 22px;
--radius-full: 9999px;
```

---

## Files Modified

### 1. `resources/views/layouts/app.blade.php`
- Reorganized CSS variables in `:root` (all light mode defaults)
- Added dark mode overrides in `html.dark`
- Removed all hardcoded colors (replaced with variables)
- Improved theme initialization JavaScript with proper hex-to-RGB conversion
- Fixed spacing/padding consistency issues

### 2. `resources/views/settings/index.blade.php`
- Updated accent color handling with hex-to-RGB conversion
- Fixed dark mode toggle functionality
- Improved theme initialization logic
- Added smooth transitions between themes

---

## How Theme Switching Works

### 1. **Page Load**

```javascript
// Initialization happens before DOM renders
function initializeTheme() {
    const currentTheme = localStorage.getItem('theme') || 'light';
    const savedAccent = localStorage.getItem('accent') || '#0fb9b1';
    
    // Set dark class if needed
    if (currentTheme === 'dark') {
        document.documentElement.classList.add('dark');
    }
    
    // Set accent colors with proper RGB
    const rgb = hexToRgb(savedAccent);
    document.documentElement.style.setProperty('--color-accent', savedAccent);
    document.documentElement.style.setProperty(
        '--color-accent-soft',
        `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, 0.14)`
    );
}
```

### 2. **Theme Toggle (settings page)**

```javascript
darkModeToggle.addEventListener('change', function() {
    const isDark = this.checked;
    document.documentElement.classList.toggle('dark', isDark);
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
});
```

### 3. **Accent Color Change (settings page)**

```javascript
accentButtons.forEach(button => {
    button.addEventListener('click', function() {
        const color = this.dataset.accent;
        const rgb = hexToRgb(color);
        
        document.documentElement.style.setProperty('--color-accent', color);
        document.documentElement.style.setProperty(
            '--color-accent-soft',
            `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, 0.14)`
        );
        localStorage.setItem('accent', color);
    });
});
```

---

## Testing & Verification

### Light Mode
- ✅ Cards appear with white background
- ✅ Text is dark (`#18242b`)
- ✅ Padding/spacing is consistent
- ✅ Shadows are subtle
- ✅ Borders are light gray

### Dark Mode
- ✅ Cards appear with dark background (`#1a1a1a`)
- ✅ Text is light (`#e8ecef`)
- ✅ Padding/spacing is CONSISTENT (no layout breaks)
- ✅ Shadows are darker
- ✅ Borders are light gray on dark

### Theme Switching
- ✅ No layout shift when switching modes
- ✅ Accent color persists across page reloads
- ✅ Theme setting persists across page reloads
- ✅ Smooth transitions (0.25s ease)

### Responsive
- ✅ Mobile (480px): Layout preserved
- ✅ Tablet (768px): Layout preserved
- ✅ Desktop (1024px+): Layout preserved

---

## Best Practices for Future Development

### 1. **Always Use Variables**
❌ Bad:
```css
background: white;
color: #333;
```

✅ Good:
```css
background: var(--color-surface);
color: var(--color-text);
```

### 2. **For Temporary Styling During Dev**
If you must use inline styles (e.g., Vue component):
```html
<div :style="{ background: 'var(--color-surface)', color: 'var(--color-text)' }"></div>
```

### 3. **Adding New Components**
```css
.new-component {
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    color: var(--color-text);
    padding: var(--spacing-lg);
    border-radius: var(--radius-md);
    transition: var(--transition-base);
}

.new-component:hover {
    background: var(--color-surface-soft);
}
```

### 4. **Accent Color Combinations**
When creating accent-based elements:
```css
.accent-element {
    background: var(--color-accent);
    color: white;  /* High contrast on accent background */
}

.accent-soft {
    background: var(--color-accent-soft);
    color: var(--color-accent);
}
```

### 5. **Bootstrap Override**
All Bootstrap classes are overridden with our theme variables:
```css
.btn-primary {
    background: var(--color-accent);
}

.form-control {
    background: var(--color-surface-strong);
    color: var(--color-text);
}

.card {
    background: var(--color-surface);
}
```

---

## Migration Checklist for Future Components

When adding new pages/components:

- [ ] Use `var(--color-*)` for all colors
- [ ] Use `var(--spacing-*)` for all margins/padding
- [ ] Use `var(--font-size-*)` and `var(--font-weight-*)` for typography
- [ ] Use `var(--radius-*)` for border-radius
- [ ] Use `var(--shadow-*)` for box-shadow
- [ ] Use `var(--transition-*)` for transitions
- [ ] Test in both light and dark modes
- [ ] Verify no hardcoded `#fff`, `#000`, `white`, `black`
- [ ] Verify padding/spacing doesn't break on theme switch

---

## Customization

### To Change Brand Color
Edit `:root` in `app.blade.php`:
```css
:root {
    --color-primary: #NEW_COLOR;  /* Change all brand elements */
}
```

### To Change Accent Color (User Preference)
Already implemented in settings page! Users can select from:
- Teal: `#0fb9b1`
- Blue: `#4facfe`
- Amber: `#f18f01`
- Purple: `#8d6e9f`

### To Add New Accent Option
1. Add button in `resources/views/settings/index.blade.php`:
```html
<button type="button" class="accent-chip btn btn-sm" data-accent="#YOUR_COLOR">Your Color</button>
```

2. Add to CSS variables (if needed for reference)

---

## Performance Notes

- CSS variables are natively supported in all modern browsers
- Theme switching is instant (no full page reload)
- No JavaScript calculations for rendering (all computed by browser)
- Minimal payload size (variables are not duplicated)

---

## Known Limitations

1. **IE11 Support**: CSS variables not supported (but this project targets modern browsers)
2. **Custom Accent Colors**: Currently limited to predefined palette (can be extended)
3. **Per-Component Themes**: Currently global theme only (component-level theming not implemented)

---

## References

- CSS Variables (Custom Properties): https://developer.mozilla.org/en-US/docs/Web/CSS/--*
- Color Models (hex, RGB, RGBA): https://developer.mozilla.org/en-US/docs/Web/CSS/color
- Dark Mode: https://developer.mozilla.org/en-US/docs/Web/CSS/@media/prefers-color-scheme

