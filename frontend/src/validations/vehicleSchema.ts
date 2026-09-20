import * as yup from 'yup'

export const vehicleSchema = yup.object({
  plate_number: yup
    .string()
    .required('Plate number is required')
    .max(20, 'Plate number too long'),
  vehicle_type: yup
    .string()
    .required('Vehicle type is required')
    .oneOf(['motorcycle', 'car', 'suv', 'van', 'truck', 'bus'], 'Invalid type'),
  color: yup.string().nullable().max(30),
  make: yup.string().nullable().max(50),
  model: yup.string().nullable().max(50),
  owner_name: yup.string().nullable().max(100),
  owner_phone: yup.string().nullable().max(20),
  is_vip: yup.boolean().default(false),
  is_blacklisted: yup.boolean().default(false),
  blacklist_reason: yup.string().nullable().max(255),
})

export type VehicleFormData = yup.InferType<typeof vehicleSchema>
