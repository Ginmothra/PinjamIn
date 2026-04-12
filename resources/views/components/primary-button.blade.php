<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-[linear-gradient(120deg,#20a7db_0%,#007dff_62%,#ffffff_130%)] border border-[#007dff]/35 rounded-md font-semibold text-xs text-[#00356d] uppercase tracking-widest shadow-[0_8px_20px_rgba(0,125,255,0.20)] hover:shadow-[0_10px_24px_rgba(0,125,255,0.26)] focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
