<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Publication;
use Symfony\Component\HttpFoundation\Response;

class CheckDeadline
{
    /**
     * Handle an incoming request and evaluate publication deadline status.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $publicationId = $request->route('publication') ?? $request->input('publication_id');

        if ($publicationId) {
            $pub = is_object($publicationId) ? $publicationId : Publication::find($publicationId);

            if ($pub && $pub->hard_deadline) {
                // Hard deadline passed: lock mutations if not an approver/admin
                if (now()->greaterThan($pub->hard_deadline) && $request->isMethod('post', 'put', 'patch', 'delete')) {
                    return back()->with('error', "BATAS WAKTU KERAS (Hard Deadline) telah berakhir pada {$pub->hard_deadline->format('d M Y H:i')}. Pengunggahan dan penyuntingan data telah dikunci otomatis oleh sistem.");
                }

                // Soft deadline notice: attach warning to session
                if ($pub->soft_deadline && now()->greaterThan($pub->soft_deadline) && now()->lessThanOrEqualTo($pub->hard_deadline)) {
                    session()->flash('deadline_warning', "Perhatian: Publikasi '{$pub->title}' telah melewati Soft Deadline ({$pub->soft_deadline->format('d M Y')}). Mohon segera selesaikan sebelum Hard Deadline ({$pub->hard_deadline->format('d M Y')}).");
                }
            }
        }

        return $next($request);
    }
}
