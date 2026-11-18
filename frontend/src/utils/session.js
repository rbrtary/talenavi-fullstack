export const LOGIN_STORAGE_KEY = 'user'

export function saveLoginSession(email) {
  const session = {
    email,
    loginAt: new Date().toISOString(),
  }

  localStorage.setItem(LOGIN_STORAGE_KEY, JSON.stringify(session))

  return session
}