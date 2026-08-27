@extends('layouts.cliente')

@section('title', 'Ficha presencial')

@section('content')
@php
    $planNombre = $planActivo?->plan?->nombre ?? 'Sin membresía';
    $estado = strtoupper((string) $cliente->estado_cliente);
    $activo = in_array($estado, ['ACTIVO', 'ACTIVA'], true);
    $diasPlan = $kpis['dias_plan'] ?? null;
    $venceLabel = $planActivo?->fecha_fin
        ? 'Activo hasta '.$planActivo->fecha_fin->translatedFormat('F Y')
        : 'Sin fecha de vencimiento';
    $tabs = [
        'resumen' => 'Resumen',
        'personal' => 'Personal',
        'membresia' => 'Membresía',
        'entrenamiento' => 'Entrenamiento',
        'progreso' => 'Progreso',
        'asistencia' => 'Asistencia',
        'pagos' => 'Pagos',
    ];
    $tab = in_array($tab ?? '', array_keys($tabs), true) ? $tab : 'resumen';
    $coachNombre = trim(($cliente->coach?->usuario?->nombres ?? '').' '.($cliente->coach?->usuario?->apellidos ?? '')) ?: 'Sin coach';
    $horarioLabel = $cliente->horario?->etiqueta() ?? 'Sin horario asignado';
@endphp

<section class="cv-hero cv-hero--perfil">
    <div class="cv-avatar cv-avatar--xl">
        @if ($user->avatarUrl())
            <img src="{{ $user->avatarUrl() }}" alt="{{ $user->nombres }}">
        @else
            {{ $user->iniciales() }}
        @endif
    </div>

    <div>
        <div class="cv-kicker">Expediente presencial · FigueFit</div>
        <h1 class="cv-title">{{ $user->nombres }} {{ $user->apellidos }}</h1>
        <p class="cv-sub">{{ $planNombre }} · {{ $venceLabel }}</p>
        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:12px">
            <span class="cv-badge {{ $activo ? 'is-ok' : 'is-pink' }}">{{ $cliente->estado_cliente }}</span>
            <span class="cv-badge is-pink">PRESENCIAL</span>
            <span class="cv-badge is-cyan">{{ $planNombre }}</span>
            @if ($cliente->objetivo_principal)
                <span class="cv-badge">{{ $cliente->objetivo_principal }}</span>
            @endif
        </div>
    </div>

    <div class="cv-hero__actions">
        <a class="cv-btn cv-btn-sm cv-btn-ghost" href="{{ route('cliente.presencial.perfil', ['tab' => 'personal']) }}">Editar perfil</a>
        @if ($yaCheckinHoy)
            <span class="cv-btn cv-btn-sm cv-btn-ghost" style="opacity:.7;cursor:default">Asistencia hoy ✓</span>
        @else
            <form method="POST" action="{{ route('cliente.presencial.checkin') }}" style="display:inline">
                @csrf
                <button type="submit" class="cv-btn cv-btn-sm">Registrar asistencia</button>
            </form>
        @endif
        <a class="cv-btn cv-btn-sm cv-btn-cyan" href="{{ route('cliente.progreso.create') }}">Actualizar progreso</a>
        <a class="cv-btn cv-btn-sm cv-btn-ghost" href="{{ route('cliente.comunicacion.index') }}">Contactar</a>
    </div>
</section>

<div class="cv-metrics">
    <x-cliente.metric
        label="Membresía"
        :value="$diasPlan !== null ? $diasPlan.' d' : '—'"
        :hint="$activo ? 'Días restantes' : 'Revisar estado'"
    />
    <x-cliente.metric
        label="Próximo pago"
        :value="$proximoPago ? '$'.number_format((float) $proximoPago->monto, 0, ',', '.') : 'Al día'"
        :hint="$proximoPago ? ($proximoPago->estado_pago) : 'Sin pendientes'"
    />
    <x-cliente.metric
        label="Asistencias mes"
        :value="$asistenciasMes"
        :hint="'Frecuencia '.$frecuencia.'/día'"
    />
    <x-cliente.metric
        label="Progreso"
        :value="$kpis['ultimo_peso'] ? $kpis['ultimo_peso'].' kg' : '—'"
        :hint="'Racha '.$kpis['racha'].' días'"
    />
</div>

<nav class="cv-tabs" aria-label="Secciones de ficha">
    @foreach ($tabs as $key => $label)
        <a
            class="cv-tab {{ $tab === $key ? 'is-active' : '' }}"
            href="{{ route('cliente.presencial.perfil', ['tab' => $key]) }}"
        >{{ $label }}</a>
    @endforeach
</nav>

@if ($tab === 'resumen')
    <div class="cv-grid-12">
        <div class="cv-span-7">
            <x-cliente.card title="Estado de membresía" subtitle="Plan, coach y horario de sala">
                <div class="cv-profile-rail">
                    <div class="cv-row"><span>Plan</span><strong>{{ $planNombre }}</strong></div>
                    <div class="cv-row"><span>Inicio</span><strong>{{ optional($planActivo?->fecha_inicio)->format('d/m/Y') ?: '—' }}</strong></div>
                    <div class="cv-row"><span>Vence</span><strong>{{ optional($planActivo?->fecha_fin)->format('d/m/Y') ?: '—' }}</strong></div>
                    <div class="cv-row"><span>Coach</span><strong>{{ $coachNombre }}</strong></div>
                    <div class="cv-row"><span>Horario</span><strong>{{ $horarioLabel }}</strong></div>
                    <div class="cv-row"><span>Objetivo</span><strong>{{ $cliente->objetivo_principal ?: '—' }}</strong></div>
                </div>
                @if ($diasPlan !== null && $diasPlan >= 0)
                    @php $pctPlan = min(100, max(0, (int) round(($diasPlan / max(1, (int) ($planActivo?->plan?->duracion_dias ?? 30))) * 100))); @endphp
                    <div style="margin-top:16px">
                        <div class="cv-kicker">Vigencia restante</div>
                        <div class="cv-progress" style="margin-top:8px"><i style="width:{{ $pctPlan }}%"></i></div>
                    </div>
                @endif
            </x-cliente.card>

            <x-cliente.card title="Asistencia reciente" subtitle="Últimas entradas a sala" class="cv-mt">
                @if ($serieAsistencia->isNotEmpty())
                    @php $maxA = max(1, (int) $serieAsistencia->max('total')); @endphp
                    <div class="cv-chart" role="img" aria-label="Asistencias 28 días">
                        @foreach ($serieAsistencia as $punto)
                            <span style="height: {{ max(10, ((int) $punto->total / $maxA) * 100) }}%" title="{{ $punto->fecha }} · {{ $punto->total }}"></span>
                        @endforeach
                    </div>
                @endif
                <div class="cv-table-wrap" style="margin-top:12px">
                    <table class="cv-table">
                        <thead>
                            <tr><th>Fecha</th><th>Estado</th><th>Coach</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($asistencias->take(6) as $a)
                                <tr>
                                    <td>{{ optional($a->fecha)->format('d/m/Y') }}</td>
                                    <td><span class="cv-badge is-ok">{{ $a->estado }}</span></td>
                                    <td>{{ $a->coach?->usuario?->nombres ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="cv-empty">Aún no hay asistencias. Registra tu check-in.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-cliente.card>
        </div>

        <div class="cv-span-5">
            <x-cliente.card title="Objetivos" subtitle="Metas activas">
                <div class="cv-list-feed">
                    @forelse ($metas as $meta)
                        @php
                            $actual = (float) ($meta->valor_actual ?? 0);
                            $objetivo = max(0.01, (float) ($meta->valor_objetivo ?? 1));
                            $pctMeta = min(100, round(($actual / $objetivo) * 100));
                        @endphp
                        <div class="cv-feed-item">
                            <div>
                                <strong>{{ $meta->nombre }}</strong>
                                <p style="margin:4px 0 8px;font-size:12px;color:var(--cv-muted)">
                                    {{ $meta->valor_actual }} / {{ $meta->valor_objetivo }} {{ $meta->unidad }}
                                </p>
                                <div class="cv-progress"><i style="width:{{ $pctMeta }}%"></i></div>
                            </div>
                        </div>
                    @empty
                        <p class="cv-empty">Sin metas registradas. Tu coach puede definirlas en seguimiento.</p>
                    @endforelse
                </div>
            </x-cliente.card>

            <x-cliente.card title="Comunicación" subtitle="WhatsApp y avisos" class="cv-mt">
                @forelse ($invitacionesWa as $inv)
                    <div class="cv-row" style="margin-bottom:10px">
                        <span>{{ optional($inv->mostrada_en)->format('d/m/Y') ?: 'Invitación' }}</span>
                        <strong>
                            @if ($inv->aceptada_en)
                                <span class="cv-badge is-ok">Aceptada</span>
                            @elseif ($inv->cerrada_sin_aceptar)
                                <span class="cv-badge">Cerrada</span>
                            @else
                                <a class="cv-badge is-cyan" href="{{ route('cliente.dashboard') }}">Pendiente</a>
                            @endif
                        </strong>
                    </div>
                @empty
                    <p class="cv-empty">Sin invitaciones WhatsApp recientes.</p>
                @endforelse
                <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:12px">
                    <a class="cv-btn cv-btn-sm cv-btn-ghost" href="{{ route('cliente.notificaciones.index') }}">Notificaciones</a>
                    <a class="cv-btn cv-btn-sm cv-btn-ghost" href="{{ route('cliente.tienda.index') }}">Tienda sala</a>
                </div>
            </x-cliente.card>

            <x-cliente.card title="Módulos activos" subtitle="Accesos de tu plan" class="cv-mt">
                <div style="display:flex;flex-wrap:wrap;gap:6px">
                    @forelse ($accesos as $acceso)
                        <span class="cv-badge is-cyan">{{ $acceso->modulo?->nombre ?? $acceso->modulo?->codigo ?? 'Módulo' }}</span>
                    @empty
                        <span class="cv-empty">Sin módulos activos</span>
                    @endforelse
                </div>
            </x-cliente.card>
        </div>
    </div>
@endif

@if ($tab === 'personal')
    <div class="cv-grid-12">
        <div class="cv-span-4">
            <x-cliente.card title="Identidad" subtitle="Datos visibles en sala">
                <div style="display:grid;gap:14px">
                    <div class="cv-avatar cv-avatar--xl">
                        @if ($user->avatarUrl())
                            <img src="{{ $user->avatarUrl() }}" alt="">
                        @else
                            {{ $user->iniciales() }}
                        @endif
                    </div>
                    <div>
                        <strong>{{ $user->nombres }} {{ $user->apellidos }}</strong>
                        <p style="margin:4px 0 0;color:var(--cv-muted);font-size:13px">{{ $user->email }}</p>
                    </div>
                    <div class="cv-profile-rail">
                        <div class="cv-row"><span>Documento</span><strong>{{ $cliente->documento ?: '—' }}</strong></div>
                        <div class="cv-row"><span>Teléfono</span><strong>{{ $user->telefono ?: '—' }}</strong></div>
                        <div class="cv-row"><span>Ciudad</span><strong>{{ $cliente->ciudad ?: '—' }}</strong></div>
                        <div class="cv-row"><span>Nacimiento</span><strong>{{ optional($cliente->fecha_nacimiento)->format('d/m/Y') ?: '—' }}</strong></div>
                    </div>
                </div>
            </x-cliente.card>
        </div>
        <div class="cv-span-8">
            <x-cliente.card title="Editar ficha" subtitle="Actualiza contacto, salud y preferencias">
                <form method="POST" action="{{ route('cliente.presencial.update') }}" class="cv-form-stack">
                    @csrf
                    @method('PUT')
                    <div class="cv-kicker">Datos personales</div>
                    <div class="cv-form">
                        <label>Nombres<input name="nombres" value="{{ old('nombres', $user->nombres) }}" required></label>
                        <label>Apellidos<input name="apellidos" value="{{ old('apellidos', $user->apellidos) }}" required></label>
                        <label>Teléfono<input name="telefono" value="{{ old('telefono', $user->telefono) }}"></label>
                        <label>Ciudad<input name="ciudad" value="{{ old('ciudad', $cliente->ciudad) }}"></label>
                        <label>Fecha nacimiento<input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', optional($cliente->fecha_nacimiento)->format('Y-m-d')) }}"></label>
                        <label>Género
                            <select name="genero">
                                @foreach (['' => '—', 'FEMENINO' => 'Femenino', 'MASCULINO' => 'Masculino', 'OTRO' => 'Otro'] as $val => $lab)
                                    <option value="{{ $val }}" @selected(old('genero', $cliente->genero) === $val || old('genero', $cliente->genero) === $lab)>{{ $lab }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="cv-span-2">Objetivo<input name="objetivo_principal" value="{{ old('objetivo_principal', $cliente->objetivo_principal) }}"></label>
                    </div>

                    <div class="cv-kicker" style="margin-top:18px">Salud y emergencia</div>
                    <div class="cv-form">
                        <label>Peso inicial (kg)<input type="number" step="0.1" name="peso_inicial_kg" value="{{ old('peso_inicial_kg', $cliente->peso_inicial_kg) }}"></label>
                        <label>Estatura (cm)<input type="number" step="0.1" name="estatura_cm" value="{{ old('estatura_cm', $cliente->estatura_cm) }}"></label>
                        <label class="cv-span-2">Alergias<textarea name="alergias" rows="2">{{ old('alergias', $cliente->alergias) }}</textarea></label>
                        <label class="cv-span-2">Condiciones<textarea name="condiciones_medicas" rows="2">{{ old('condiciones_medicas', $cliente->condiciones_medicas) }}</textarea></label>
                        <label class="cv-span-2">Lesiones<textarea name="lesiones" rows="2">{{ old('lesiones', $cliente->lesiones) }}</textarea></label>
                        <label>Contacto emergencia<input name="contacto_emergencia_nombre" value="{{ old('contacto_emergencia_nombre', $cliente->contacto_emergencia_nombre) }}"></label>
                        <label>Tel. emergencia<input name="contacto_emergencia_telefono" value="{{ old('contacto_emergencia_telefono', $cliente->contacto_emergencia_telefono) }}"></label>
                    </div>

                    <div class="cv-kicker" style="margin-top:18px">Preferencias</div>
                    <div class="cv-form">
                        <label class="cv-check"><input type="checkbox" name="notif_email" value="1" @checked(old('notif_email', $preferencias->notif_email))> Email</label>
                        <label class="cv-check"><input type="checkbox" name="notif_push" value="1" @checked(old('notif_push', $preferencias->notif_push))> Push</label>
                        <label class="cv-check"><input type="checkbox" name="notif_whatsapp" value="1" @checked(old('notif_whatsapp', $preferencias->notif_whatsapp))> WhatsApp</label>
                        <label>Horario preferido<input name="horario_preferido" value="{{ old('horario_preferido', $preferencias->horario_preferido) }}"></label>
                    </div>

                    <div style="margin-top:16px;display:flex;gap:8px">
                        <button type="submit" class="cv-btn">Guardar cambios</button>
                    </div>
                </form>
            </x-cliente.card>

            <x-cliente.card title="Seguridad" subtitle="Cambio de contraseña" class="cv-mt">
                <form method="POST" action="{{ route('cliente.presencial.password') }}" class="cv-form">
                    @csrf
                    @method('PUT')
                    <label>Contraseña actual<input type="password" name="password_actual" required></label>
                    <label>Nueva contraseña<input type="password" name="password" required></label>
                    <label>Confirmar<input type="password" name="password_confirmation" required></label>
                    <div class="cv-span-2"><button type="submit" class="cv-btn cv-btn-ghost">Actualizar contraseña</button></div>
                </form>
            </x-cliente.card>
        </div>
    </div>
@endif

@if ($tab === 'membresia')
    <div class="cv-grid-12">
        <div class="cv-span-5">
            <x-cliente.card title="Plan actual" subtitle="Beneficios y vigencia">
                <div class="cv-profile-rail">
                    <div class="cv-row"><span>Nombre</span><strong>{{ $planNombre }}</strong></div>
                    <div class="cv-row"><span>Estado</span><strong>{{ $planActivo?->estado_plan_cliente ?? '—' }}</strong></div>
                    <div class="cv-row"><span>Inicio</span><strong>{{ optional($planActivo?->fecha_inicio)->format('d/m/Y') ?: '—' }}</strong></div>
                    <div class="cv-row"><span>Fin</span><strong>{{ optional($planActivo?->fecha_fin)->format('d/m/Y') ?: '—' }}</strong></div>
                    <div class="cv-row"><span>Precio ref.</span><strong>{{ $planActivo?->plan?->precio ? '$'.number_format((float) $planActivo->plan->precio, 0, ',', '.') : '—' }}</strong></div>
                </div>
                @if ($planActivo?->plan?->descripcion)
                    <p style="margin-top:14px;color:var(--cv-muted);font-size:13px">{{ $planActivo->plan->descripcion }}</p>
                @endif
                <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:16px">
                    <a class="cv-btn cv-btn-sm" href="{{ route('cliente.comercial.planes') }}">Renovar / cambiar</a>
                    <a class="cv-btn cv-btn-sm cv-btn-ghost" href="{{ route('cliente.plan.index') }}">Detalle membresía</a>
                </div>
            </x-cliente.card>
        </div>
        <div class="cv-span-7">
            <x-cliente.card title="Historial de planes" subtitle="Compras y renovaciones">
                <div class="cv-table-wrap">
                    <table class="cv-table">
                        <thead>
                            <tr><th>Plan</th><th>Inicio</th><th>Fin</th><th>Estado</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($historialPlanes as $hp)
                                <tr>
                                    <td>{{ $hp->plan?->nombre ?? '—' }}</td>
                                    <td>{{ optional($hp->fecha_inicio)->format('d/m/Y') }}</td>
                                    <td>{{ optional($hp->fecha_fin)->format('d/m/Y') }}</td>
                                    <td><span class="cv-badge">{{ $hp->estado_plan_cliente }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="cv-empty">Sin historial de planes.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-cliente.card>
        </div>
    </div>
@endif

@if ($tab === 'entrenamiento')
    <div class="cv-grid-12">
        <div class="cv-span-7">
            <x-cliente.card title="Plan de entrenamiento" subtitle="Asignado por tu coach">
                @if ($planEntrenamiento)
                    <div class="cv-profile-rail">
                        <div class="cv-row"><span>Plan</span><strong>{{ $planEntrenamiento->nombre ?? ('Plan #'.$planEntrenamiento->id_plan_entrenamiento) }}</strong></div>
                        <div class="cv-row"><span>Estado</span><strong>{{ $planEntrenamiento->estado ?? '—' }}</strong></div>
                        <div class="cv-row"><span>Rutinas</span><strong>{{ $planEntrenamiento->rutinas?->count() ?? 0 }}</strong></div>
                        <div class="cv-row"><span>Completadas (7d)</span><strong>{{ $rutinasSemana }}</strong></div>
                    </div>
                    <div style="margin-top:14px">
                        <a class="cv-btn cv-btn-sm" href="{{ route('cliente.entrenamiento.index') }}">Ir a entrenar</a>
                    </div>
                @else
                    <p class="cv-empty">Aún no tienes plan de entrenamiento asignado.</p>
                @endif
            </x-cliente.card>
        </div>
        <div class="cv-span-5">
            <x-cliente.card title="Fitness profile" subtitle="Objetivo y baseline">
                <div class="cv-profile-rail">
                    <div class="cv-row"><span>Objetivo</span><strong>{{ $cliente->objetivo_principal ?: '—' }}</strong></div>
                    <div class="cv-row"><span>Peso inicial</span><strong>{{ $cliente->peso_inicial_kg ? $cliente->peso_inicial_kg.' kg' : '—' }}</strong></div>
                    <div class="cv-row"><span>Estatura</span><strong>{{ $cliente->estatura_cm ? $cliente->estatura_cm.' cm' : '—' }}</strong></div>
                    <div class="cv-row"><span>Coach</span><strong>{{ $coachNombre }}</strong></div>
                    <div class="cv-row"><span>Restricciones</span><strong>{{ $cliente->lesiones ?: ($cliente->condiciones_medicas ?: 'Ninguna') }}</strong></div>
                </div>
            </x-cliente.card>
        </div>
    </div>
@endif

@if ($tab === 'progreso')
    <div class="cv-metrics" style="margin-bottom:16px">
        <x-cliente.metric label="Último peso" :value="$progresos->first()?->peso_kg ? $progresos->first()->peso_kg.' kg' : '—'" />
        <x-cliente.metric label="IMC" :value="$progresos->first()?->imc ?? '—'" />
        <x-cliente.metric label="Registros" :value="$progresos->count()" />
        <x-cliente.metric label="Metas" :value="$metas->count()" />
    </div>

    @if ($seriePeso->isNotEmpty())
        @php $maxP = max(1, (float) $seriePeso->max('peso_kg')); @endphp
        <x-cliente.card title="Evolución de peso" subtitle="Tendencia reciente">
            <div class="cv-chart">
                @foreach ($seriePeso as $punto)
                    <span style="height: {{ max(8, ((float) $punto->peso_kg / $maxP) * 100) }}%" title="{{ $punto->peso_kg }} kg"></span>
                @endforeach
            </div>
        </x-cliente.card>
    @endif

    <div class="cv-grid-12" style="margin-top:16px">
        <div class="cv-span-7">
            <x-cliente.card title="Historial de medidas" subtitle="Últimos registros">
                <div class="cv-table-wrap">
                    <table class="cv-table">
                        <thead>
                            <tr><th>Fecha</th><th>Peso</th><th>IMC</th><th>Cintura</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($progresos as $reg)
                                <tr>
                                    <td>{{ optional($reg->fecha_registro)->format('d/m/Y') }}</td>
                                    <td>{{ $reg->peso_kg ? $reg->peso_kg.' kg' : '—' }}</td>
                                    <td>{{ $reg->imc ?? '—' }}</td>
                                    <td>{{ $reg->cintura_cm ? $reg->cintura_cm.' cm' : '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="cv-empty">Sin progreso. Registra tu primera medición.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div style="margin-top:12px">
                    <a class="cv-btn cv-btn-sm" href="{{ route('cliente.progreso.create') }}">Nuevo registro</a>
                    <a class="cv-btn cv-btn-sm cv-btn-ghost" href="{{ route('cliente.progreso.index') }}">Ver todo</a>
                </div>
            </x-cliente.card>
        </div>
        <div class="cv-span-5">
            <x-cliente.card title="Metas" subtitle="Objetivos alcanzados / en curso">
                @forelse ($metas as $meta)
                    @php
                        $actual = (float) ($meta->valor_actual ?? 0);
                        $objetivo = max(0.01, (float) ($meta->valor_objetivo ?? 1));
                        $pctMeta = min(100, round(($actual / $objetivo) * 100));
                    @endphp
                    <div style="margin-bottom:14px">
                        <strong>{{ $meta->nombre }}</strong>
                        <p style="margin:4px 0 8px;font-size:12px;color:var(--cv-muted)">{{ $meta->estado }} · {{ $meta->valor_actual }}/{{ $meta->valor_objetivo }} {{ $meta->unidad }}</p>
                        <div class="cv-progress"><i style="width:{{ $pctMeta }}%"></i></div>
                    </div>
                @empty
                    <p class="cv-empty">Sin metas.</p>
                @endforelse
            </x-cliente.card>
        </div>
    </div>
@endif

@if ($tab === 'asistencia')
    @php
        $inicioMes = now()->startOfMonth();
        $diasMesCal = $inicioMes->daysInMonth;
        $offset = $inicioMes->dayOfWeekIso; // 1=lun
    @endphp
    <div class="cv-grid-12">
        <div class="cv-span-5">
            <x-cliente.card title="Check-in de hoy" subtitle="Registro de asistencia en sala">
                <div class="cv-metrics" style="grid-template-columns:1fr 1fr;margin-bottom:14px">
                    <x-cliente.metric label="Este mes" :value="$asistenciasMes" hint="Entradas" />
                    <x-cliente.metric label="Racha" :value="$kpis['racha']" hint="Días seguidos" />
                </div>
                @if ($yaCheckinHoy)
                    <p class="cv-empty" style="color:var(--cv-ok)">Ya registraste tu asistencia hoy.</p>
                @else
                    <form method="POST" action="{{ route('cliente.presencial.checkin') }}">
                        @csrf
                        <div class="cv-form" style="margin-bottom:12px">
                            <label class="cv-span-2">Nota (opcional)
                                <input name="observacion" maxlength="255" placeholder="Ej. sesión pierna">
                            </label>
                        </div>
                        <button type="submit" class="cv-btn">Registrar asistencia</button>
                    </form>
                @endif
            </x-cliente.card>
        </div>
        <div class="cv-span-7">
            <x-cliente.card title="Calendario {{ now()->translatedFormat('F Y') }}" subtitle="Días con asistencia marcada">
                <div class="cv-cal">
                    @foreach (['L','M','X','J','V','S','D'] as $d)
                        <div class="cv-cal__head">{{ $d }}</div>
                    @endforeach
                    @for ($i = 1; $i < $offset; $i++)
                        <div class="cv-cal__day is-empty"></div>
                    @endfor
                    @for ($day = 1; $day <= $diasMesCal; $day++)
                        @php
                            $fecha = $inicioMes->copy()->day($day)->toDateString();
                            $presente = $mapaAsistencia->has($fecha);
                            $esHoy = $fecha === now()->toDateString();
                        @endphp
                        <div class="cv-cal__day {{ $presente ? 'is-on' : '' }} {{ $esHoy ? 'is-today' : '' }}">{{ $day }}</div>
                    @endfor
                </div>
            </x-cliente.card>
        </div>
        <div class="cv-span-12">
            <x-cliente.card title="Historial de asistencias" subtitle="Últimas 40 entradas">
                <div class="cv-table-wrap">
                    <table class="cv-table">
                        <thead>
                            <tr><th>Fecha</th><th>Estado</th><th>Coach</th><th>Nota</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($asistencias as $a)
                                <tr>
                                    <td>{{ optional($a->fecha)->format('d/m/Y') }}</td>
                                    <td><span class="cv-badge is-ok">{{ $a->estado }}</span></td>
                                    <td>{{ trim(($a->coach?->usuario?->nombres ?? '').' '.($a->coach?->usuario?->apellidos ?? '')) ?: '—' }}</td>
                                    <td>{{ $a->observacion ?: '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="cv-empty">Sin historial.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-cliente.card>
        </div>
    </div>
@endif

@if ($tab === 'pagos')
    <div class="cv-grid-12">
        <div class="cv-span-4">
            <x-cliente.card title="Estado de pagos" subtitle="Próximo cobro">
                @if ($proximoPago)
                    <div class="cv-profile-rail">
                        <div class="cv-row"><span>Monto</span><strong>${{ number_format((float) $proximoPago->monto, 0, ',', '.') }}</strong></div>
                        <div class="cv-row"><span>Estado</span><strong>{{ $proximoPago->estado_pago }}</strong></div>
                        <div class="cv-row"><span>Plan</span><strong>{{ $proximoPago->plan?->nombre ?? '—' }}</strong></div>
                        <div class="cv-row"><span>Creado</span><strong>{{ optional($proximoPago->creado_en)->format('d/m/Y') }}</strong></div>
                    </div>
                @else
                    <p class="cv-empty" style="color:var(--cv-ok)">No tienes pagos pendientes.</p>
                @endif
                <div style="margin-top:14px">
                    <a class="cv-btn cv-btn-sm" href="{{ route('cliente.pagos.index') }}">Ver facturas</a>
                </div>
            </x-cliente.card>
        </div>
        <div class="cv-span-8">
            <x-cliente.card title="Historial de pagos" subtitle="Facturas y estados">
                <div class="cv-table-wrap">
                    <table class="cv-table">
                        <thead>
                            <tr><th>Fecha</th><th>Plan</th><th>Monto</th><th>Estado</th><th>Método</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($pagos as $pago)
                                <tr>
                                    <td>{{ optional($pago->creado_en)->format('d/m/Y') }}</td>
                                    <td>{{ $pago->plan?->nombre ?? '—' }}</td>
                                    <td>${{ number_format((float) $pago->monto, 0, ',', '.') }}</td>
                                    <td>
                                        @php
                                            $st = strtoupper((string) $pago->estado_pago);
                                            $badge = $st === 'APROBADO' || $st === 'PAGADO' ? 'is-ok' : ($st === 'PENDIENTE' ? 'is-cyan' : 'is-pink');
                                        @endphp
                                        <span class="cv-badge {{ $badge }}">{{ $pago->estado_pago }}</span>
                                    </td>
                                    <td>{{ $pago->metodo_pago ?? $pago->metodo ?? $pago->pasarela ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="cv-empty">Sin pagos registrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-cliente.card>
        </div>
    </div>
@endif
@endsection
