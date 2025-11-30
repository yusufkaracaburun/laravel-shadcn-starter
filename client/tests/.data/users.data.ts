export interface TestUser {
  name: string
  email: string
  password: string
}

export const testusers: Record<string, TestUser> = {
  super_admin: {
    name: 'Super Admin User',
    email: 'super-admin@example.com',
    password: 'password',
  },
  admin: {
    name: 'Admin User',
    email: 'admin@example.com',
    password: 'password',
  },
  customer: {
    name: 'Customer User',
    email: 'customer@example.com',
    password: 'password',
  },
  contractor: {
    name: 'Contractor User',
    email: 'contractor@example.com',
    password: 'password',
  },
  test: {
    name: 'Test User',
    email: 'test@example.com',
    password: 'password',
  },
}
