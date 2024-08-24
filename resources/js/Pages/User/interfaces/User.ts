import { Role } from '@/RolePermission/interfaces/Role'
import { Permission } from '@/RolePermission/interfaces/Permission'

export interface User {
    id: number,
    name: string,
    email: string,
    email_verified_at: DateTime,
    created_at: DateTime,
    updated_at: DateTime,
    highest_role: string,
    roles: Role[],
    permissions: Permission[],
    unassigned_permissions: Permission[]
}
