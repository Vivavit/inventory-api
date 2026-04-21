# CSS Variables Quick Reference

## How to Use

All CSS variables are defined in `resources/css/design-system.css` and are automatically available to all pages.

### Example Usage

```css
.my-component {
  background: var(--bg-primary);     /* Automatically switches in dark mode */
  color: var(--text-primary);         /* Readable in both modes */
  padding: var(--spacing-xl);         /* Consistent spacing */
  border: 1px solid var(--border-color);  /* Proper borders */
  box-shadow: var(--shadow-md);       /* Professional shadows */
  border-radius: var(--radius-lg);    /* Consistent curves */
  transition: all var(--transition-base);  /* Smooth animations */
}
```

---

## Available Variables

### Colors - Semantic

```css
--primary: #0D9488        /* Brand color */
--primary-dark: #0F766E   /* Darker variant */
--primary-light: #14B8A6  /* Lighter variant */

--secondary: #6366F1      /* Secondary accent */
--success: #10B981        /* Success states */
--warning: #F59E0B        /* Warnings */
--danger: #EF4444         /* Errors */
--info: #3B82F6           /* Information */
```

### Background Colors (Light/Dark Mode)

```css
/* Light Mode (default) */
--bg-primary: #ffffff     /* Main background */
--bg-secondary: #f9fafb   /* Secondary background */
--bg-tertiary: #f3f4f6    /* Tertiary background */
--bg-hover: #f0f1f2       /* Hover state */

/* Dark Mode (auto switches) */
html.dark {
  --bg-primary: #0f172a      /* Dark main */
  --bg-secondary: #1e293b    /* Dark secondary */
  --bg-tertiary: #334155     /* Dark tertiary */
  --bg-hover: #475569        /* Dark hover */
}
```

### Text Colors (Light/Dark Mode)

```css
/* Light Mode */
--text-primary: #1f2937    /* Main text */
--text-secondary: #6b7280  /* Secondary text */
--text-tertiary: #9ca3af   /* Tertiary text (muted) */
--text-invert: #ffffff     /* On dark backgrounds */

/* Dark Mode */
html.dark {
  --text-primary: #f1f5f9     /* Light text */
  --text-secondary: #cbd5e1   /* Light secondary */
  --text-tertiary: #94a3b8    /* Light muted */
  --text-invert: #0f172a      /* On light backgrounds */
}
```

### Border Colors (Light/Dark Mode)

```css
/* Light Mode */
--border-color: #e5e7eb        /* Standard border */
--border-color-strong: #d1d5db /* Emphasize border */
--border-color-light: #f3f4f6   /* Light border */

/* Dark Mode */
html.dark {
  --border-color: #334155        /* Dark border */
  --border-color-strong: #475569 /* Dark strong */
  --border-color-light: #1e293b   /* Dark light */
}
```

### Shadows

```css
--shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05)
--shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1)
--shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1)
--shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1)
--shadow-2xl: 0 25px 50px -12px rgba(0, 0, 0, 0.15)

/* Dark Mode (stronger) */
html.dark {
  --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.3)
  --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.4)
  --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.5)
  --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.6)
  --shadow-2xl: 0 25px 50px -12px rgba(0, 0, 0, 0.75)
}
```

### Spacing

```css
--spacing-xs: 4px      /* Extra small */
--spacing-sm: 8px      /* Small */
--spacing-md: 12px     /* Medium */
--spacing-lg: 16px     /* Large */
--spacing-xl: 20px     /* Extra large */
--spacing-2xl: 24px    /* 2X large */
--spacing-3xl: 32px    /* 3X large */
--spacing-4xl: 40px    /* 4X large */
--spacing-5xl: 48px    /* 5X large */
```

### Border Radius

```css
--radius-sm: 6px       /* Small radius */
--radius-md: 10px      /* Medium radius */
--radius-lg: 14px      /* Large radius */
--radius-xl: 16px      /* Extra large */
--radius-2xl: 20px     /* 2X large */
--radius-3xl: 24px     /* 3X large */
--radius-full: 9999px  /* Fully rounded */
```

### Typography

```css
/* Font Family */
--font-family: 'Instrument Sans', system-ui, -apple-system, sans-serif

/* Font Sizes */
--font-size-xs: 11px    /* Extra small */
--font-size-sm: 12px    /* Small */
--font-size-base: 13px  /* Base */
--font-size-md: 14px    /* Medium */
--font-size-lg: 16px    /* Large */
--font-size-xl: 18px    /* Extra large */
--font-size-2xl: 20px   /* 2X large */
--font-size-3xl: 24px   /* 3X large */
--font-size-4xl: 28px   /* 4X large */
--font-size-5xl: 36px   /* 5X large */

/* Font Weights */
--font-weight-normal: 400      /* Normal */
--font-weight-medium: 500      /* Medium */
--font-weight-semibold: 600    /* Semibold */
--font-weight-bold: 700        /* Bold */
--font-weight-extrabold: 800   /* Extra bold */
```

### Transitions

```css
--transition-fast: 0.15s ease   /* Fast animation */
--transition-base: 0.25s ease   /* Default animation */
--transition-slow: 0.35s ease   /* Slow animation */
```

### Line Heights

```css
--line-height-tight: 1.2        /* Tight spacing */
--line-height-normal: 1.5       /* Normal spacing */
--line-height-relaxed: 1.75     /* Relaxed spacing */
```

---

## Component Usage Examples

### Button
```css
.btn-custom {
  padding: var(--spacing-md) var(--spacing-xl);
  background: var(--primary);
  color: var(--text-invert);
  border-radius: var(--radius-md);
  font-weight: var(--font-weight-semibold);
  box-shadow: var(--shadow-md);
  transition: all var(--transition-base);
}

.btn-custom:hover {
  background: var(--primary-dark);
  box-shadow: var(--shadow-lg);
  transform: translateY(-2px);
}
```

### Card
```css
.card {
  background: var(--bg-primary);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-2xl);
  padding: var(--spacing-2xl);
  box-shadow: var(--shadow-sm);
  transition: all var(--transition-base);
}

.card:hover {
  box-shadow: var(--shadow-md);
  transform: translateY(-4px);
}
```

### Table
```css
.table thead {
  background: var(--bg-secondary);
}

.table th {
  padding: var(--spacing-lg) var(--spacing-xl);
  color: var(--text-secondary);
  font-weight: var(--font-weight-bold);
  font-size: var(--font-size-sm);
  border-bottom: 2px solid var(--border-color-strong);
}

.table td {
  padding: var(--spacing-lg) var(--spacing-xl);
  border-bottom: 1px solid var(--border-color);
  color: var(--text-primary);
}

.table tbody tr:hover {
  background: var(--bg-secondary);
}
```

### Form Input
```css
input,
select,
textarea {
  padding: var(--spacing-md) var(--spacing-lg);
  border: 1.5px solid var(--border-color-strong);
  border-radius: var(--radius-md);
  background: var(--bg-primary);
  color: var(--text-primary);
  font-family: var(--font-family);
  font-size: var(--font-size-md);
  transition: all var(--transition-fast);
}

input:focus,
select:focus,
textarea:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.1);
}
```

### Modal
```css
.modal-content {
  background: var(--bg-primary);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-3xl);
  box-shadow: var(--shadow-2xl);
}

.modal-header {
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
  color: var(--text-invert);
  padding: var(--spacing-2xl);
}

.modal-body {
  padding: var(--spacing-2xl);
}
```

### Badge
```css
.badge {
  padding: var(--spacing-sm) var(--spacing-lg);
  border-radius: var(--radius-full);
  font-size: var(--font-size-xs);
  font-weight: var(--font-weight-bold);
  background: linear-gradient(135deg, rgba(13, 148, 136, 0.15), rgba(20, 184, 166, 0.05));
  color: var(--primary);
}
```

---

## Dark Mode Automatic Switching

All variables automatically switch when `.dark` class is added to `<html>`:

```javascript
// Toggle dark mode
document.documentElement.classList.toggle('dark');

// Enable dark mode
document.documentElement.classList.add('dark');

// Disable dark mode
document.documentElement.classList.remove('dark');
```

### All Components Update Automatically

No extra CSS needed! Just add `.dark` class:

```css
/* Light Mode (default) */
.card { background: var(--bg-primary); }  /* #ffffff */

/* Dark Mode (auto) */
html.dark  /* --bg-primary now: #0f172a */
```

---

## Responsive Spacing

For different screen sizes:

```css
:root {
  --spacing-lg: 16px;  /* Desktop default */
}

@media (max-width: 1024px) {
  :root {
    --spacing-lg: 14px;  /* Tablets */
  }
}

@media (max-width: 768px) {
  :root {
    --spacing-lg: 12px;  /* Mobile */
  }
}
```

---

## Tips & Best Practices

✅ **DO:**
- Use variables for everything (colors, spacing, shadows, etc.)
- Create component classes that use variables
- Test in both light and dark modes
- Use semantic variable names

❌ **DON'T:**
- Hardcode colors (use `--color-*` variables instead)
- Mix units (use variables for consistency)
- Override variables with inline styles
- Create page-specific color variables

---

## Verification Checklist

Before deploying, verify:

- [ ] All text is readable in light mode
- [ ] All text is readable in dark mode
- [ ] No white backgrounds in dark mode
- [ ] Borders are visible in both modes
- [ ] Shadows render correctly
- [ ] Buttons have clear hover states
- [ ] Forms are usable in both modes
- [ ] Tables are scannable in both modes
- [ ] Spacing is consistent
- [ ] No hardcoded colors visible in CSS

---

## Debugging

### Check Current Variables

```javascript
// In browser console
const styles = getComputedStyle(document.documentElement);
console.log(styles.getPropertyValue('--bg-primary'));
console.log(styles.getPropertyValue('--text-primary'));
```

### Test Dark Mode

```javascript
// Toggle dark mode
document.documentElement.classList.toggle('dark');

// Check if class is set
console.log(document.documentElement.classList);

// Inspect variables
const styles = getComputedStyle(document.documentElement);
console.log(styles.getPropertyValue('--bg-primary'));
```

### Debug Specific Element

```javascript
// Check what color is actually being used
console.log(getComputedStyle(document.querySelector('.card')).backgroundColor);
```

---

## Reference Files

- `resources/css/design-system.css` - All variable definitions
- `resources/css/orders.css` - Example usage
- `resources/views/orders/index-refactored.blade.php` - Clean HTML example

---

Good luck! 🎨✨
