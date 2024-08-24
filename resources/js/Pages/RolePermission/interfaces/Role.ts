import Permission from './Permission'

export interface Role {
  id: number
  name: string
  guard_name: string
  created_at: DateTime
  updated_at: DateTime
  permissions: Permission[]
}
