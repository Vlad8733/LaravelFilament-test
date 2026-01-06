<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->where('user_id', Auth::id());

        // Поиск по действию
        if ($search = $request->input('search')) {
            $query->where('action', 'like', "%$search%");
        }

        // Фильтрация по типу действия
        if ($type = $request->input('type')) {
            $query->where('action', 'like', "$type:%");
        }

        $logs = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        // Для фильтрации по типам действий (уникальные action до ':')
        $rawTypes = ActivityLog::where('user_id', Auth::id())
            ->selectRaw('LEFT(action, LOCATE(":", action) - 1) as type')
            ->whereRaw('LOCATE(":", action) > 0')
            ->groupBy('type')
            ->pluck('type');

        $types = $rawTypes->map(function ($type) {
            $type = trim($type);
            // Преобразуем тип к ключу перевода
            $key = Str::snake(strtolower($type));
            $translationKey = "activity_log.log.$key";
            $label = __($translationKey);
            // Если перевод не найден, используем оригинальный текст
            if ($label === $translationKey) {
                $label = $type;
            }

            return [
                'key' => $type, // Используем оригинальный тип для фильтрации
                'label' => $label,
            ];
        });

        return view('activity_log.index', compact('logs', 'types'));
    }
}
