import * as yup from 'yup'

export const createCouponSchema = yup.object({
  code: yup
    .string()
    .required('Coupon code is required')
    .min(4, 'Code must be at least 4 characters')
    .max(20, 'Code cannot exceed 20 characters')
    .matches(/^[A-Z0-9]+$/, 'Code must be uppercase letters and numbers only'),
  type: yup
    .string()
    .required('Coupon type is required')
    .oneOf(['percentage', 'fixed_amount', 'free_hours', 'full_waiver'], 'Invalid type'),
  value: yup
    .number()
    .required('Value is required')
    .min(1, 'Value must be at least 1'),
  min_amount: yup.number().nullable().min(0),
  max_discount: yup.number().nullable().min(0),
  usage_limit: yup.number().nullable().min(1),
  per_user_limit: yup.number().min(1).default(1),
  valid_from: yup.string().nullable(),
  valid_until: yup.string().nullable(),
  description: yup.string().nullable().max(500),
})

export type CreateCouponFormData = yup.InferType<typeof createCouponSchema>
