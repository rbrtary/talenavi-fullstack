import { useRef, useState } from 'react'
import logo from '../assets/logo.png'
import { saveLoginSession } from '../utils/session.js'
import { hasErrors, validateLoginForm } from '../utils/validation.js'
import TextField from './TextField.jsx'

const initialValues = { email: '', password: '' }

export default function LoginForm() {
  const [values, setValues] = useState(initialValues)
  const [errors, setErrors] = useState({})
  const [submitted, setSubmitted] = useState(false)

  const emailRef = useRef(null)
  const passwordRef = useRef(null)

  function handleChange(event) {
    const { name, value } = event.target
    const nextValues = { ...values, [name]: value }

    setValues(nextValues)

    if (submitted) {
      setErrors(validateLoginForm(nextValues))
    }
  }

  function handleSubmit(event) {
    event.preventDefault()
    setSubmitted(true)

    const validationErrors = validateLoginForm(values)
    setErrors(validationErrors)

    if (hasErrors(validationErrors)) {
      const firstInvalid = validationErrors.email ? emailRef : passwordRef
      firstInvalid.current?.focus()
      return
    }

    const session = saveLoginSession(values.email.trim())
    console.log('Login berhasil', session)
    alert('Login berhasil!')
  }

  return (
    <form noValidate onSubmit={handleSubmit} className="w-full max-w-78">
      <img src={logo} alt="Logo Talenavi" className="mx-auto size-23" />

      <h1 className="mt-5 text-center text-xl leading-[30px] font-medium text-white text-shadow-[0_4px_4px_rgb(0_0_0/0.25)] lg:text-black">
        Selamat Datang Kembali!
      </h1>

      <TextField
        ref={emailRef}
        id="email"
        name="email"
        type="email"
        label="Email"
        placeholder="Enter your email"
        autoComplete="email"
        value={values.email}
        onChange={handleChange}
        error={errors.email}
        className="mt-18 lg:mt-11"
      />

      <TextField
        ref={passwordRef}
        id="password"
        name="password"
        type="password"
        label="Password"
        placeholder="Enter your password"
        autoComplete="current-password"
        value={values.password}
        onChange={handleChange}
        error={errors.password}
        className="mt-12 lg:mt-6"
      />

      <div className="mt-6 text-right lg:mt-3">
        <a href="#" className="text-xs leading-[18px] text-white underline lg:text-black">
          Lupa Password?
        </a>
      </div>

      <button
        type="submit"
        className="mt-1 h-10 w-full cursor-pointer rounded-lg bg-brand text-xs font-medium text-white transition-colors hover:bg-brand-dark focus-visible:ring-2 focus-visible:ring-brand/40 focus-visible:ring-offset-2 focus-visible:outline-none lg:mt-4"
      >
        Sign In
      </button>

      <p className="mt-22 text-center text-xs leading-[18px] lg:mt-12">
        <span className="text-link">Belum punya akun?</span>{' '}
        <a href="#" className="text-white underline lg:text-black">
          Daftar di sini
        </a>
      </p>
    </form>
  )
}