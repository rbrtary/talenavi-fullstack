export default function TextField({ id, label, error, className = '', ref, ...inputProps }) {
  const errorId = `${id}-error`
  const stateClass = error
    ? 'border-red-500 ring-2 ring-red-500/40 focus:ring-red-500/40'
    : 'border-field focus:border-brand focus:ring-brand/20'

  return (
    <div className={className}>
      <label htmlFor={id} className="mb-1 block text-[11px] leading-4 text-black max-lg:sr-only">
        {label}
      </label>

      <input
        id={id}
        ref={ref}
        aria-invalid={error ? 'true' : 'false'}
        aria-describedby={error ? errorId : undefined}
        className={`block h-10 w-full rounded-[10px] border bg-white px-4 text-xs text-black transition outline-none placeholder:text-placeholder focus:ring-2 lg:h-11 ${stateClass}`}
        {...inputProps}
      />

      {error && (
        <p id={errorId} role="alert" className="mt-1.5 text-[11px] leading-4 text-red-300 lg:text-red-600">
          {error}
        </p>
      )}
    </div>
  )
}