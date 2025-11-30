export interface TestUser {
  name: string
  email: string
  password: string
}

export const testusers: Record<string, TestUser> = {
  superadmin: {
    name: 'Super Admin',
    email: 'superadmin@example.com',
    password: 'password',
  },
  admin: {
    name: 'Admin',
    email: 'admin@example.com',
    password: 'password',
  },
  customer: {
    name: 'Customer',
    email: 'customer@example.com',
    password: 'password',
  },
  contractor: {
    name: 'Contractor',
    email: 'contractor@example.com',
    password: 'password',
  },
}
