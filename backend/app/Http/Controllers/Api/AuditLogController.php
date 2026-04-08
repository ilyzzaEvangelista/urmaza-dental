<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Paginated audit log list (staff). Search matches action, description, IP, user name/email/username.
     */
    public function index(Request $request)
    {
        $perPage = min(max((int) $request->query('per_page', 15), 1), 100);
        $search = trim((string) $request->query('search', ''));

        $query = AuditLog::query()
            ->with('user:id,name,email,username,role')
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if ($search !== '') {
            $like = '%'.addcslashes($search, '%_\\').'%';
            $query->where(function ($q) use ($like) {
                $q->where('action', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('ip_address', 'like', $like)
                    ->orWhereHas('user', function ($u) use ($like) {
                        $u->where('name', 'like', $like)
                            ->orWhere('email', 'like', $like)
                            ->orWhere('username', 'like', $like);
                    });
            });
        }

        return response()->json($query->paginate($perPage));
    }
}
