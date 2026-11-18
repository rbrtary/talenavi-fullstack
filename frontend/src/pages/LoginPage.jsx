import banner from '../assets/banner.jpg'
import LoginForm from '../components/LoginForm.jsx'

export default function LoginPage() {
  return (
    <main className="relative isolate min-h-dvh bg-mobile-bg font-sans lg:flex lg:items-center lg:justify-center lg:bg-page lg:p-8">
      <div className="lg:grid lg:login-card lg:grid-cols-[382fr_275fr] lg:overflow-hidden lg:rounded-[14px] lg:bg-white">
        <div className="absolute inset-0 -z-10 overflow-hidden lg:relative lg:inset-auto lg:z-auto">
          <img
            src={banner}
            alt="Tim PT Dynamic Talenta Navigator - One Spirit, One Freedom!"
            className="h-[106%] w-full object-cover object-top lg:h-full lg:object-center"
          />
          <div className="absolute inset-0 bg-black/80 lg:hidden" aria-hidden="true" />
          <div
            className="absolute inset-x-0 bottom-0 h-16 bg-linear-to-t from-mobile-bg to-transparent lg:hidden"
            aria-hidden="true"
          />
        </div>

        <section className="flex min-h-dvh items-center justify-center px-6 pt-8 pb-19 lg:min-h-0 lg:p-6">
          <LoginForm />
        </section>
      </div>
    </main>
  )
}