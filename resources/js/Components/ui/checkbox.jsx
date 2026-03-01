import * as React from "react"
import { Check } from "lucide-react"

const Checkbox = React.forwardRef(({ className, checked, onCheckedChange, ...props }, ref) => (
  <button
    type="button"
    role="checkbox"
    aria-checked={checked}
    ref={ref}
    onClick={() => onCheckedChange?.(!checked)}
    className={`peer h-5 w-5 shrink-0 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 ${
      checked ? 'bg-blue-600 dark:bg-blue-500 border-blue-600 dark:border-blue-500' : ''
    } ${className || ''}`}
    {...props}
  >
    {checked && <Check className="h-4 w-4 text-white" />}
  </button>
))
Checkbox.displayName = "Checkbox"

export { Checkbox }
