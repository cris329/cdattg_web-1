<?php

namespace Database\Factories;

use App\Models\PersonaImport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PersonaImport>
 */
class PersonaImportFactory extends Factory
{
    protected $model = PersonaImport::class;

    public function definition(): array
    {
        $userId = config('app.audit_default_user_id', 1);
        if (Schema::hasTable('users')) {
            try {
                $userId = User::query()->inRandomOrder()->value('id') ?? $userId;
            } catch (\Exception $e) {
                $userId = config('app.audit_default_user_id', 1);
            }
        }

        $totalRows = $this->faker->numberBetween(10, 500);
        $processedRows = $this->faker->numberBetween(0, $totalRows);
        $successCount = $this->faker->numberBetween(0, $processedRows);
        $duplicateCount = $this->faker->numberBetween(0, $processedRows - $successCount);
        $missingContactCount = $this->faker->numberBetween(0, $processedRows - $successCount - $duplicateCount);

        $statuses = ['pending', 'processing', 'completed', 'failed'];

        return [
            'user_id' => $userId,
            'original_name' => $this->faker->word().'.csv',
            'disk' => 'local',
            'path' => 'imports/'.$this->faker->uuid().'.csv',
            'total_rows' => $totalRows,
            'processed_rows' => $processedRows,
            'success_count' => $successCount,
            'duplicate_count' => $duplicateCount,
            'missing_contact_count' => $missingContactCount,
            'status' => $this->faker->randomElement($statuses),
            'error_message' => $this->faker->optional()->sentence(),
        ];
    }
}
