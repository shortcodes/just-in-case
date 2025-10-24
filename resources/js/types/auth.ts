/**
 * Form data for the Login view
 */
export interface LoginFormData {
  /** User's email address */
  email: string
  /** User's password */
  password: string
  /** Whether to remember the user's session */
  remember: boolean
}

/**
 * Page props for the Login view (passed from Inertia)
 */
export interface LoginPageProps {
  /** Flash message to display (e.g., after password reset) */
  status?: string
  /** Whether the password reset functionality is available */
  canResetPassword: boolean
}

/**
 * Form data for the Register view
 */
export interface RegisterFormData {
  /** User's full name */
  name: string
  /** User's email address */
  email: string
  /** User's password */
  password: string
  /** Password confirmation */
  password_confirmation: string
}

/**
 * Page props for the Register view (passed from Inertia)
 */
export interface RegisterPageProps {
  // Brak dodatkowych props - używamy tylko globalnych shared props
}
