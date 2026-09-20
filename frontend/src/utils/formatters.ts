/**
 * Format amount from smallest currency unit (cents) to display string.
 */
export function formatCurrency(amount: number, currency = '$'): string {
  return `${currency}${(amount / 100).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
}

/**
 * Format duration in minutes to human readable.
 */
export function formatDuration(minutes: number | null): string {
  if (!minutes) return '-'
  const h = Math.floor(minutes / 60)
  const m = minutes % 60
  return h > 0 ? `${h}h ${m}m` : `${m}m`
}

/**
 * Format ISO date string to locale display.
 */
export function formatDate(date: string): string {
  return new Date(date).toLocaleString('en-US', {
    dateStyle: 'medium',
    timeStyle: 'short',
  })
}

/**
 * Format ISO date string to short time only.
 */
export function formatTime(date: string): string {
  return new Date(date).toLocaleString('en-US', { timeStyle: 'short' })
}

/**
 * Format ISO date string to date only.
 */
export function formatDateOnly(date: string): string {
  return new Date(date).toLocaleDateString('en-US', { dateStyle: 'medium' })
}
