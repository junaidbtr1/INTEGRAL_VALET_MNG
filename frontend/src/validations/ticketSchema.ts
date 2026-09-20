import * as yup from 'yup'

export const createTicketSchema = yup.object({
  vehicle_plate: yup
    .string()
    .required('Vehicle plate number is required')
    .min(3, 'Plate number too short')
    .max(20, 'Plate number too long'),
  vehicle_type: yup
    .string()
    .required('Vehicle type is required')
    .oneOf(['motorcycle', 'car', 'suv', 'van', 'truck', 'bus'], 'Invalid vehicle type'),
  vehicle_color: yup.string().nullable(),
  parking_slot_id: yup.number().nullable(),
  notes: yup.string().nullable().max(500, 'Notes cannot exceed 500 characters'),
})

export type CreateTicketFormData = yup.InferType<typeof createTicketSchema>
