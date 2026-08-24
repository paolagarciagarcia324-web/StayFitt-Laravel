<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\Comida;
use App\Models\PropuestaPlan;
use App\Support\ResolvesStaffCoach;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class NutricionPropuestaController extends Controller
{
    use ResolvesStaffCoach;

    public function update(Request $request, PropuestaPlan $propuesta): RedirectResponse
    {
        $this->guardEditable($propuesta);
        $this->ensureNutricion($propuesta);

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'objetivo' => ['required', 'string', 'max:120'],
            'kcal_objetivo' => ['nullable', 'integer', 'min:0'],
            'proteinas_g' => ['nullable', 'numeric', 'min:0'],
            'carbohidratos_g' => ['nullable', 'numeric', 'min:0'],
            'grasas_g' => ['nullable', 'numeric', 'min:0'],
            'recomendaciones' => ['nullable', 'string'],
        ]);

        $propuesta->planNutricional->update($data + [
            'estado' => 'BORRADOR',
            'actualizado_en' => now(),
        ]);

        if ($propuesta->estado === PropuestaPlan::ESTADO_BORRADOR_ENTRENAMIENTO) {
            $propuesta->update([
                'estado' => PropuestaPlan::ESTADO_BORRADOR_NUTRICION,
                'actualizado_en' => now(),
            ]);
        }

        return back()->with('success', 'Plan nutricional guardado.');
    }

    public function storeComida(Request $request, PropuestaPlan $propuesta): RedirectResponse
    {
        $this->guardEditable($propuesta);
        $this->ensureNutricion($propuesta);

        $data = $this->validatedComida($request);
        $orden = (int) Comida::query()
            ->where('id_plan_nutricional', $propuesta->id_plan_nutricional)
            ->max('orden') + 1;

        Comida::create($data + [
            'id_plan_nutricional' => $propuesta->id_plan_nutricional,
            'orden' => $orden,
        ]);

        return back()->with('success', 'Comida agregada.');
    }

    public function updateComida(Request $request, PropuestaPlan $propuesta, int $comida): RedirectResponse
    {
        $this->guardEditable($propuesta);
        $model = $this->findComida($propuesta, $comida);
        $model->update($this->validatedComida($request));

        return back()->with('success', 'Comida actualizada.');
    }

    public function destroyComida(PropuestaPlan $propuesta, int $comida): RedirectResponse
    {
        $this->guardEditable($propuesta);
        $this->findComida($propuesta, $comida)->delete();

        return back()->with('success', 'Comida eliminada.');
    }

    private function validatedComida(Request $request): array
    {
        return $request->validate([
            'tipo_comida' => ['required', 'in:DESAYUNO,ALMUERZO,CENA,SNACK,MERIENDA,OTRO'],
            'nombre' => ['required', 'string', 'max:150'],
            'receta' => ['nullable', 'string'],
            'porcion' => ['nullable', 'string', 'max:80'],
            'calorias' => ['nullable', 'numeric', 'min:0'],
            'proteinas_g' => ['nullable', 'numeric', 'min:0'],
            'carbohidratos_g' => ['nullable', 'numeric', 'min:0'],
            'grasas_g' => ['nullable', 'numeric', 'min:0'],
        ]);
    }

    private function guardEditable(PropuestaPlan $propuesta): void
    {
        if (! $this->userCanAccessPropuesta(Auth::user(), $propuesta)) {
            abort(403);
        }

        if (! $propuesta->esEditable()) {
            abort(403, 'La propuesta no es editable.');
        }
    }

    private function ensureNutricion(PropuestaPlan $propuesta): void
    {
        if ($propuesta->id_plan_nutricional) {
            $propuesta->load('planNutricional');

            return;
        }

        $nutricion = \App\Models\PlanNutricional::create([
            'id_cliente' => null,
            'id_coach' => $propuesta->id_coach,
            'id_propuesta' => $propuesta->id_propuesta,
            'nombre' => 'Nutrición · '.$propuesta->titulo_interno,
            'objetivo' => '',
            'estado' => 'BORRADOR',
        ]);

        $propuesta->update([
            'id_plan_nutricional' => $nutricion->id_plan_nutricional,
            'estado' => PropuestaPlan::ESTADO_BORRADOR_NUTRICION,
            'actualizado_en' => now(),
        ]);
        $propuesta->setRelation('planNutricional', $nutricion);
    }

    private function findComida(PropuestaPlan $propuesta, int $id): Comida
    {
        return Comida::query()
            ->where('id_plan_nutricional', $propuesta->id_plan_nutricional)
            ->where('id_comida', $id)
            ->firstOrFail();
    }
}
