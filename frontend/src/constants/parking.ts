export const VEHICLE_TYPES = [
  { value: 'motorcycle', label: 'Motorcycle' },
  { value: 'car', label: 'Car' },
  { value: 'suv', label: 'SUV' },
  { value: 'van', label: 'Van' },
  { value: 'truck', label: 'Truck' },
  { value: 'bus', label: 'Bus' },
] as const

export const SLOT_TYPES = [
  { value: 'standard', label: 'Standard' },
  { value: 'compact', label: 'Compact' },
  { value: 'large', label: 'Large' },
  { value: 'disabled', label: 'Disabled' },
  { value: 'ev_charging', label: 'EV Charging' },
  { value: 'vip', label: 'VIP' },
  { value: 'reserved', label: 'Reserved' },
] as const

export const TICKET_STATUSES = [
  { value: '', label: 'All Statuses' },
  { value: 'active', label: 'Active' },
  { value: 'completed', label: 'Completed' },
  { value: 'closed', label: 'Closed' },
  { value: 'cancelled', label: 'Cancelled' },
  { value: 'lost_ticket', label: 'Lost Ticket' },
  { value: 'overstay', label: 'Overstay' },
] as const

export const PAYMENT_METHODS = [
  { value: 'cash', label: 'Cash', icon: 'pi pi-money-bill' },
  { value: 'card', label: 'Card', icon: 'pi pi-credit-card' },
  { value: 'digital', label: 'Digital', icon: 'pi pi-mobile' },
] as const

export const COUPON_TYPES = [
  { value: 'percentage', label: 'Percentage Discount' },
  { value: 'fixed_amount', label: 'Fixed Amount Off' },
  { value: 'free_hours', label: 'Free Hours' },
  { value: 'full_waiver', label: 'Full Waiver (100% free)' },
] as const

export const FEATURE_LIST = [
  { key: 'coupon_system', label: 'Coupon System', desc: 'Enable discount coupons' },
  { key: 'ticket_printing', label: 'Ticket Printing', desc: 'Print physical tickets' },
  { key: 'qr_code', label: 'QR Code', desc: 'QR code on tickets' },
  { key: 'barcode', label: 'Barcode', desc: 'Barcode on tickets' },
  { key: 'sms_notifications', label: 'SMS', desc: 'SMS alerts' },
  { key: 'email_notifications', label: 'Email', desc: 'Email receipts' },
  { key: 'valet_tracking', label: 'Valet Tracking', desc: 'GPS tracking' },
  { key: 'vehicle_photo_capture', label: 'Vehicle Photo', desc: 'Photo at entry' },
  { key: 'multi_floor', label: 'Multi-Floor', desc: 'Multiple floors' },
  { key: 'reserved_slots', label: 'Reserved Slots', desc: 'Slot reservations' },
] as const

export const SETTINGS_TABS = [
  { key: 'general', label: 'General', icon: 'pi pi-cog' },
  { key: 'branding', label: 'Branding', icon: 'pi pi-palette' },
  { key: 'features', label: 'Features', icon: 'pi pi-sliders-h' },
  { key: 'pricing', label: 'Pricing', icon: 'pi pi-wallet' },
] as const
