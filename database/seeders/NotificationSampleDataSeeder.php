<?php

namespace Database\Seeders;

use App\Models\Alert;
use App\Models\AppNotification;
use App\Models\Dgu;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * In-app уведомления за последние 5 календарных дней (для /notifications).
 */
class NotificationSampleDataSeeder extends Seeder
{
    public function run(): void
    {
        AppNotification::query()->delete();

        $recipients = User::query()
            ->whereHas('roles', function ($q): void {
                $q->where('guard_name', 'web')
                    ->whereIn('name', ['admin', 'operator']);
            })
            ->get();

        if ($recipients->isEmpty()) {
            return;
        }

        $dgus = Dgu::query()->orderBy('id')->limit(12)->get();
        $alertIds = Alert::query()->orderBy('id')->pluck('id')->all();

        $tz = config('app.timezone', 'UTC');
        $today = Carbon::now($tz)->startOfDay();

        $templates = [
            [-4, 8, 5, 'Открыта тревога', null, 'alert_opened', true, 0, 0],
            [-4, 11, 40, 'Открыта тревога', null, 'alert_opened', false, 1, 1],
            [-4, 16, 20, 'Система', 'Проверка почтового канала завершена.', 'system_check', true, null, null],
            [-3, 9, 15, 'Открыта тревога', null, 'alert_opened', true, 2, 2],
            [-3, 13, 0, 'Открыта тревога', null, 'alert_opened', false, 3, 3],
            [-3, 18, 45, 'Открыта тревога', null, 'alert_opened', true, 0, 4],
            [-2, 7, 30, 'Открыта тревога', null, 'alert_opened', false, 4, 5],
            [-2, 12, 10, 'Система', 'Фоновая синхронизация справочников.', 'system_info', true, null, null],
            [-2, 15, 55, 'Открыта тревога', null, 'alert_opened', true, 5, 6],
            [-1, 8, 0, 'Открыта тревога', null, 'alert_opened', false, 1, 7],
            [-1, 14, 25, 'Открыта тревога', null, 'alert_opened', true, 6, 8],
            [-1, 19, 10, 'Открыта тревога', null, 'alert_opened', false, 7, 9],
            [0, 6, 50, 'Открыта тревога', null, 'alert_opened', false, 2, 10],
            [0, 10, 15, 'Открыта тревога', null, 'alert_opened', true, 3, 11],
        ];

        foreach ($recipients as $user) {
            foreach ($templates as $ti => [
                $dayOffset, $h, $m, $title, $body, $type, $read, $dguIdx, $alertIdx,
            ]) {
                $at = $today->copy()->addDays($dayOffset)->setTime($h, $m, 0);

                $dgu = is_int($dguIdx) && $dgus->isNotEmpty()
                    ? $dgus[$dguIdx % $dgus->count()]
                    : null;

                $bodyText = $body;
                if ($bodyText === null && $dgu) {
                    $bodyText = $dgu->name ?? $dgu->serial_number;
                }

                $data = null;
                if ($type === 'alert_opened' && $alertIds !== []) {
                    $aid = $alertIds[$alertIdx % count($alertIds)];
                    $data = ['alert_id' => $aid];
                }

                $readAt = $read ? $at->copy()->addMinutes(15 + ($ti % 40)) : null;

                DB::table('app_notifications')->insert([
                    'user_id' => $user->id,
                    'dgu_id' => $dgu?->id,
                    'type' => $type,
                    'title' => $title,
                    'body' => $bodyText,
                    'data' => $data !== null ? json_encode($data) : null,
                    'read_at' => $readAt?->format('Y-m-d H:i:s'),
                    'created_at' => $at->format('Y-m-d H:i:s'),
                    'updated_at' => $at->format('Y-m-d H:i:s'),
                ]);
            }
        }
    }
}
