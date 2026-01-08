<?php

namespace App\Console\Commands;

use App\Models\PendingRegistration;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CleanupPendingRegistrations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:pending-registrations {--hours=24 : Age in hours before cleanup}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Nettoie les inscriptions en attente (pending) de plus de X heures';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hours = (int) $this->option('hours');
        $cutoffDate = Carbon::now()->subHours($hours);

        $this->info("Recherche des inscriptions pending de plus de {$hours} heures...");

        // Trouver les inscriptions pending expirées
        $expiredRegistrations = PendingRegistration::where('status', 'pending')
            ->where('created_at', '<', $cutoffDate)
            ->get();

        if ($expiredRegistrations->isEmpty()) {
            $this->info('✅ Aucune inscription pending expirée trouvée.');
            return 0;
        }

        $count = $expiredRegistrations->count();
        $this->warn("⚠️  {$count} inscription(s) pending expirée(s) trouvée(s).");

        // Afficher le détail
        $this->table(
            ['ID', 'Email', 'Plan', 'Créée le', 'Âge'],
            $expiredRegistrations->map(function ($reg) {
                return [
                    $reg->id,
                    $reg->email,
                    $reg->plan->name ?? 'N/A',
                    $reg->created_at->format('Y-m-d H:i:s'),
                    $reg->created_at->diffForHumans(),
                ];
            })
        );

        if ($this->confirm('Voulez-vous marquer ces inscriptions comme "expired" ?', true)) {
            // Marquer comme expired au lieu de supprimer (pour audit)
            PendingRegistration::where('status', 'pending')
                ->where('created_at', '<', $cutoffDate)
                ->update([
                    'status' => 'expired',
                    'completed_at' => now(),
                ]);

            $this->info("✅ {$count} inscription(s) marquée(s) comme expired.");
        } else {
            $this->warn('Opération annulée.');
        }

        // Optionnel: Supprimer les anciennes entrées (> 30 jours)
        $oldCutoff = Carbon::now()->subDays(30);
        $oldCount = PendingRegistration::whereIn('status', ['expired', 'failed'])
            ->where('created_at', '<', $oldCutoff)
            ->count();

        if ($oldCount > 0 && $this->confirm("Supprimer définitivement {$oldCount} ancien(s) enregistrement(s) de plus de 30 jours ?", false)) {
            PendingRegistration::whereIn('status', ['expired', 'failed'])
                ->where('created_at', '<', $oldCutoff)
                ->delete();

            $this->info("✅ {$oldCount} ancien(s) enregistrement(s) supprimé(s).");
        }

        return 0;
    }
}
