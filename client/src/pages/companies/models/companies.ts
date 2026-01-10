/**
 * Company models matching backend Company model exactly
 * @see api/app/Models/Company.php
 * @see api/app/Http/Resources/CompanyResource.php
 */

export enum ECompanyStatus {
  ACTIVE = 'active',
  INACTIVE = 'inactive',
  PENDING = 'pending',
}
export type TCompanyStatus = (typeof ECompanyStatus)[keyof typeof ECompanyStatus]

export enum ECompanyIndustry {
  TECHNOLOGY = 'technology',
  FINANCE = 'finance',
  HEALTHCARE = 'healthcare',
  RETAIL = 'retail',
  MANUFACTURING = 'manufacturing',
  EDUCATION = 'education',
}
export type TCompanyIndustry =
  (typeof ECompanyIndustry)[keyof typeof ECompanyIndustry]

export enum ECompanyEmployeeSize {
  ONE_TO_TEN = '1-10',
  ELEVEN_TO_FIFTY = '11-50',
  FIFTY_ONE_TO_TWO_HUNDRED = '51-200',
  TWO_HUNDRED_ONE_TO_FIVE_HUNDRED = '201-500',
  FIVE_HUNDRED_PLUS = '500+',
}
export type TCompanyEmployeeSize =
  (typeof ECompanyEmployeeSize)[keyof typeof ECompanyEmployeeSize]

export interface ICompany {
  id: number
  name: string
  email: string
  phone: string | null
  industry: TCompanyIndustry
  status: TCompanyStatus
  employees: TCompanyEmployeeSize
  team_id: number | null
  created_at: string
  updated_at: string
}

export interface ICreateCompanyRequest {
  name: string
  email: string
  phone?: string | null
  industry: TCompanyIndustry
  status: TCompanyStatus
  employees: TCompanyEmployeeSize
}

export interface IUpdateCompanyRequest {
  name?: string
  email?: string
  phone?: string | null
  industry?: TCompanyIndustry
  status?: TCompanyStatus
  employees?: TCompanyEmployeeSize
}

export interface ICompanyFilters {
  name?: string
  email?: string
  industry?: TCompanyIndustry
  status?: TCompanyStatus
  created_at?: string
  search?: string
  between?: string
}
