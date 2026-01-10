import { z } from 'zod'

import {
  ECompanyStatus,
  ECompanyIndustry,
  ECompanyEmployeeSize,
} from '../models/companies'

/**
 * Company status schema matching backend validation
 * @see api/app/Http/Requests/StoreCompanyRequest.php
 * @see api/app/Http/Requests/UpdateCompanyRequest.php
 */
export const companyStatusSchema = z.nativeEnum(ECompanyStatus)
export type TCompanyStatus = z.infer<typeof companyStatusSchema>

/**
 * Company industry schema matching backend validation
 * @see api/app/Http/Requests/StoreCompanyRequest.php
 */
export const companyIndustrySchema = z.nativeEnum(ECompanyIndustry)
export type TCompanyIndustry = z.infer<typeof companyIndustrySchema>

/**
 * Company employee size schema matching backend validation
 * @see api/app/Http/Requests/StoreCompanyRequest.php
 */
export const companyEmployeeSizeSchema = z.nativeEnum(ECompanyEmployeeSize)
export type TCompanyEmployeeSize = z.infer<typeof companyEmployeeSizeSchema>

/**
 * Company schema matching backend CompanyResource structure
 * @see api/app/Http/Resources/CompanyResource.php
 * @see api/app/Models/Company.php
 */
export const companySchema = z.object({
  id: z.number(),
  name: z.string(),
  email: z.string(),
  phone: z.string().nullable(),
  industry: companyIndustrySchema,
  status: companyStatusSchema,
  employees: companyEmployeeSizeSchema,
  team_id: z.number().nullable(),
  created_at: z.string(),
  updated_at: z.string(),
})

export type Company = z.infer<typeof companySchema>

export const companyListSchema = z.array(companySchema)

/**
 * Base schema for company form (shared between create and edit modes)
 * @see api/app/Http/Requests/StoreCompanyRequest.php
 * @see api/app/Http/Requests/UpdateCompanyRequest.php
 */
const baseCompanyFormSchema = z.object({
  name: z.string().min(1, 'Name is required.').max(255, 'Name must not exceed 255 characters.'),
  email: z
    .string()
    .email('Please enter a valid email address.')
    .min(1, 'Email is required.')
    .max(255, 'Email must not exceed 255 characters.'),
  phone: z.string().max(255, 'Phone must not exceed 255 characters.').optional().nullable(),
  industry: companyIndustrySchema,
  status: companyStatusSchema,
  employees: companyEmployeeSizeSchema,
})

/**
 * Schema for creating a new company
 * @see api/app/Http/Requests/StoreCompanyRequest.php
 */
export const createCompanyFormSchema = baseCompanyFormSchema

/**
 * Schema for editing an existing company
 * @see api/app/Http/Requests/UpdateCompanyRequest.php
 */
export const editCompanyFormSchema = baseCompanyFormSchema.partial()

export type TCreateCompanyForm = z.infer<typeof createCompanyFormSchema>
export type TEditCompanyForm = z.infer<typeof editCompanyFormSchema>
