const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/
export const PASSWORD_MIN_LENGTH = 6

export function validateEmail(email) {
  const value = email.trim()

  if (!value) return 'Email wajib diisi.'
  if (!EMAIL_PATTERN.test(value)) return 'Format email tidak valid.'
  return ''
}

export function validatePassword(password) {
  if (!password) return 'Password wajib diisi.'
  if (password.length < PASSWORD_MIN_LENGTH) {
    return `Password minimal ${PASSWORD_MIN_LENGTH} karakter.`
  }
  return ''
}

export function validateLoginForm({ email, password }) {
  return {
    email: validateEmail(email),
    password: validatePassword(password),
  }
}

export function hasErrors(errors) {
  return Object.values(errors).some(Boolean)
}