<x-app-layout>
    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Technician Dashboard</h1>
                    <p class="mt-2 text-slate-600 font-medium">Pantau dan kerjakan perbaikan barang yang rusak.</p>
                </div>
            </div>

            @if(session('status'))
                <div class="mb-6 rounded-xl bg-emerald-50 p-4 border border-emerald-100">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-emerald-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-bold text-emerald-800">{{ session('status') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                <!-- 1. BARANG BUTUH PERBAIKAN (DAMAGED) -->
                <div class="space-y-6">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-100 text-rose-600 font-bold">!</span>
                        <h2 class="text-xl font-bold text-slate-800">Menunggu Perbaikan</h2>
                    </div>

                    @forelse($repairs->where('status', 'pending') as $repair)
                        <div class="group relative overflow-hidden rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200 transition-all hover:shadow-md">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                                        {{ $repair->item->category->name }}
                                    </span>
                                    <h3 class="text-lg font-extrabold text-slate-900">{{ $repair->item->name }}</h3>
                                    <p class="text-xs font-mono text-slate-400">#{{ $repair->item->code }}</p>
                                </div>
                                <span class="rounded-lg bg-rose-50 px-3 py-1 text-xs font-bold text-rose-600">RUSAK</span>
                            </div>

                            <div class="mb-6 rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-bold text-slate-400 uppercase mb-1">Deskripsi Kerusakan:</p>
                                <p class="text-sm text-slate-700 leading-relaxed italic">"{{ $repair->description }}"</p>
                            </div>

                            <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                                <div class="text-xs text-slate-400">
                                    Dilaporkan: {{ $repair->created_at->diffForHumans() }}
                                </div>
                                <form method="POST" action="{{ route('technician.repairs.start', $repair) }}">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white hover:bg-slate-800 transition-all transform active:scale-95">
                                        Mulai Perbaiki
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-12 bg-white rounded-3xl border-2 border-dashed border-slate-200">
                            <p class="text-slate-400 font-medium">Tidak ada barang rusak.</p>
                        </div>
                    @endforelse
                </div>

                <!-- 2. SEDANG DIPERBAIKI (MAINTENANCE) -->
                <div class="space-y-6">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100 text-amber-600 font-bold">⚙️</span>
                        <h2 class="text-xl font-bold text-slate-800">Sedang Dikerjakan</h2>
                    </div>

                    @forelse($repairs->where('status', 'in_progress') as $repair)
                        <div class="group relative overflow-hidden rounded-3xl bg-white p-6 shadow-sm ring-1 ring-amber-200 border-l-4 border-amber-400 transition-all">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-lg font-extrabold text-slate-900">{{ $repair->item->name }}</h3>
                                    <p class="text-xs font-mono text-slate-400">Mulai dikerjakan: {{ $repair->started_at->format('d M, H:i') }}</p>
                                </div>
                                <span class="rounded-lg bg-amber-50 px-3 py-1 text-xs font-bold text-amber-600 animate-pulse">UNDER MAINTENANCE</span>
                            </div>

                            <form method="POST" action="{{ route('technician.repairs.complete', $repair) }}" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Catatan Perbaikan:</label>
                                    <textarea name="repair_notes" rows="3" class="w-full rounded-2xl border-slate-200 bg-slate-50 text-sm focus:ring-amber-500 focus:border-amber-500" placeholder="Apa saja yang diperbaiki?">{{ $repair->repair_notes }}</textarea>
                                </div>

                                <div class="flex gap-3">
                                    <button type="submit" name="action" value="update" formAction="{{ route('technician.repairs.update', $repair) }}" class="flex-1 rounded-xl bg-white border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-all">
                                        Update Progress
                                    </button>
                                    <button type="submit" class="flex-1 rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-bold text-white hover:bg-amber-600 shadow-lg shadow-amber-200 transition-all transform active:scale-95">
                                        Selesai Perbaiki
                                    </button>
                                </div>
                            </form>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-12 bg-white rounded-3xl border-2 border-dashed border-slate-200">
                            <p class="text-slate-400 font-medium">Belum ada pengerjaan aktif.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
