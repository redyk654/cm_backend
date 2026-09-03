<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Certificat d'aptitude {{ $reference }}</title>
    <style>
        @page { margin: 16mm 15mm; }
        * { box-sizing: border-box; }
        body {
            font-family: "DejaVu Serif", "Times New Roman", serif;
            font-size: 11px;
            line-height: 1.45;
            color: #1a1a1a;
        }
        .lh { width: 100%; border-bottom: 1px solid #333; padding-bottom: 6px; margin-bottom: 4px; }
        .lh td { vertical-align: middle; }
        .lh .logo { width: 60px; text-align: center; }
        .lh .org { text-align: center; }
        .lh .org .name { font-weight: bold; font-size: 15px; }
        .lh .org .slogan { font-style: italic; font-size: 11px; }
        h1.doc {
            text-align: center; font-size: 14.5px; font-weight: bold;
            text-decoration: underline; margin: 22px 0 16px; letter-spacing: .5px;
        }
        p.lead { margin: 0 0 9px; text-align: justify; }
        .fill { font-weight: bold; }
        .dots { color: #888; }
        .gt { font-weight: bold; margin: 14px 0 3px; }
        table.exam { width: 100%; border-collapse: collapse; }
        table.exam td { border: 1px solid #333; vertical-align: top; padding: 6px 8px; width: 25%; }
        .box {
            display: inline-block; width: 9px; height: 9px; border: 1px solid #333;
            text-align: center; line-height: 8px; font-size: 9px; font-weight: bold; margin-right: 3px;
            font-family: "DejaVu Sans", sans-serif;
        }
        .sub { color: #444; font-size: 10px; }
        .kv { width: 100%; margin-top: 10px; }
        .kv td { padding: 4px 0; }
        .kv .lab { white-space: nowrap; width: 1%; padding-right: 6px; }
        .kv .val { border-bottom: 1px dotted #999; }
        .apt { margin: 14px 0 4px; }
        .apt span { margin-right: 20px; white-space: nowrap; }
        table.sign { width: 100%; border-collapse: collapse; margin-top: 16px; }
        table.sign td { border: 1px solid #333; padding: 8px 10px; height: 62px; vertical-align: top; width: 50%; }
        table.sign .lab { font-weight: bold; }
        .legal {
            position: fixed; bottom: 0; left: 0; right: 0;
            border-top: 1px solid #333; padding-top: 5px;
            text-align: center; font-size: 8px; line-height: 1.4; color: #444;
        }
    </style>
</head>
<body>
@php
    $moisFr = [
        1 => 'janvier', 2 => 'février', 3 => 'mars', 4 => 'avril',
        5 => 'mai', 6 => 'juin', 7 => 'juillet', 8 => 'août',
        9 => 'septembre', 10 => 'octobre', 11 => 'novembre', 12 => 'décembre',
    ];
    $dateFr = function ($valeur) use ($moisFr) {
        if (empty($valeur)) {
            return '—';
        }
        $d = \Illuminate\Support\Carbon::parse($valeur);
        $jour = $d->day === 1 ? '1er' : (string) $d->day;

        return $jour.' '.$moisFr[(int) $d->month].' '.$d->year;
    };
    $code = $c->type_visite_code ?? null;
    $coche = fn ($v) => $code === $v ? '<span class="box">&#10003;</span>' : '<span class="box">&nbsp;</span>';
    $legal = $centre['legal'] ?? [];
@endphp

    <table class="lh">
        <tr>
            <td class="logo">
                @if (!empty($centre['logo_path']) && file_exists(public_path($centre['logo_path'])))
                    <img src="{{ public_path($centre['logo_path']) }}" style="max-width:54px;max-height:54px;">
                @else
                    <span style="font-family:'DejaVu Sans',sans-serif;font-size:26px;">&#9877;</span>
                @endif
            </td>
            <td class="org">
                <div class="name">{{ $centre['raison_sociale'] ?? 'Centre Médical' }}</div>
                <div class="slogan">«&nbsp;{{ $centre['slogan'] ?? '' }}&nbsp;»</div>
            </td>
            <td class="logo">
                @if (!empty($centre['logo_path']) && file_exists(public_path($centre['logo_path'])))
                    <img src="{{ public_path($centre['logo_path']) }}" style="max-width:54px;max-height:54px;">
                @else
                    <span style="font-family:'DejaVu Sans',sans-serif;font-size:26px;">&#9877;</span>
                @endif
            </td>
        </tr>
    </table>

    <h1 class="doc">CERTIFICAT D'APTITUDE MÉDICALE</h1>

    <p class="lead">
        Je soussigné <span class="fill">{{ $c->medecin_nom }}</span> en service au Centre Médical
        et médecine du travail de {{ $centre['lieu_medecine_travail'] ?? 'Bali' }}.
    </p>
    <p class="lead">
        Certifie avoir examiné ce jour Mr/Mme/Mlle
        <span class="fill">{{ trim($c->patient_prenom.' '.$c->patient_nom) }}</span>,
        né(e) le <span class="fill">{{ $dateFr($c->patient_date_naissance) }}</span>,
        au poste de <span class="fill">{{ $c->poste ?: '—' }}</span>
        et déclare que l'intéressé(e) a subi un bilan médical au compte de
        <span class="fill">{{ $c->employeur ?: '—' }}</span>.
    </p>

    <div class="gt">Nature de l'examen</div>
    <table class="exam">
        <tr>
            <td>{!! $coche('EMBAUCHE') !!} Visite d'embauche</td>
            <td>{!! $coche('PERIODIQUE') !!} Visite périodique</td>
            <td>{!! $coche('REPRISE') !!} Visite de reprise</td>
            <td>{!! ($code === 'DEMANDE_EMPLOYEUR' || $code === 'DEMANDE_MEDECIN') ? '<span class="box">&#10003;</span>' : '<span class="box">&nbsp;</span>' !!} Visite à la demande :</td>
        </tr>
        <tr>
            <td></td>
            <td class="sub">Date de la précédente visite périodique :
                <span class="fill">{{ $c->date_precedente_visite_periodique ? $dateFr($c->date_precedente_visite_periodique) : '—' }}</span>
            </td>
            <td>{!! $coche('MALADIE_PRO') !!} Visite pour maladie professionnelle</td>
            <td class="sub">
                {!! $code === 'DEMANDE_EMPLOYEUR' ? '<span class="box">&#10003;</span>' : '<span class="box">&nbsp;</span>' !!} De l'employeur<br>
                {!! $code === 'DEMANDE_MEDECIN' ? '<span class="box">&#10003;</span>' : '<span class="box">&nbsp;</span>' !!} Du médecin du travail <span style="font-size:9px">(en cas d'inaptitude envisagée)</span>
            </td>
        </tr>
        <tr>
            <td></td>
            <td class="sub">Le cas échéant ; date du précédent examen de nature médicale : <span class="dots">—</span></td>
            <td>{!! $coche('ACCIDENT_TRAVAIL') !!} Accident de travail</td>
            <td></td>
        </tr>
    </table>

    <table class="kv">
        <tr>
            <td class="lab">Recommandations :</td>
            <td class="val"><span class="fill">{{ $c->recommandations ?: 'RAS' }}</span></td>
        </tr>
        <tr>
            <td class="lab">Poste de travail sollicité :</td>
            <td class="val"><span class="fill">{{ $c->poste ?: '—' }}</span></td>
        </tr>
    </table>

    @php
        $dl = mb_strtoupper($c->decision_label);
        $isApte = $dl === 'APTE';
        $isApteTmp = str_contains($dl, 'APTE') && str_contains($dl, 'TEMPORAIRE');
        $isInaptTmp = str_contains($dl, 'INAPTITUDE') && str_contains($dl, 'TEMPORAIRE');
        $isInaptDef = str_contains($dl, 'INAPTITUDE') && (str_contains($dl, 'DÉFINITIVE') || str_contains($dl, 'DEFINITIVE'));
        $isRestr = str_contains($dl, 'RESTRICTION');
        $ck = fn ($b) => $b ? '<span class="box">&#10003;</span>' : '<span class="box">&nbsp;</span>';
    @endphp
    <div class="apt">
        <span>{!! $ck($isApte) !!} Apte</span>
        <span>{!! $ck($isApteTmp) !!} Apte temporaire</span>
        <span>{!! $ck($isInaptTmp) !!} Inaptitude temporaire</span>
        <span>{!! $ck($isInaptDef) !!} Inaptitude définitive</span>
    </div>
    <table class="kv">
        <tr>
            <td class="lab">{!! $ck($isRestr) !!} Apte avec restriction (préciser) :</td>
            <td class="val"><span class="fill">{{ $isRestr ? ($c->restriction ?: '—') : '' }}</span></td>
        </tr>
    </table>

    <p style="margin-top:12px;">
        <span class="box">&#10003;</span>
        Période de validité : <span class="fill">{{ $c->duree_validite_mois }} mois{{ (int) $c->duree_validite_mois === 12 ? ' (1 an)' : '' }}</span>
        — expire le <span class="fill">{{ $dateFr($c->date_expiration) }}</span>.
    </p>

    <table class="sign">
        <tr>
            <td><span class="lab">Date de l'examen :</span><br><span class="fill">{{ $dateFr($c->date_examen) }}</span></td>
            <td><span class="lab">Nom et signature du médecin</span><br><span class="fill">{{ $c->medecin_nom }}</span></td>
        </tr>
    </table>

    <div class="legal">
        {{ $legal['forme_capital'] ?? '' }} — {{ $legal['siege'] ?? '' }}<br>
        RCCM : {{ $legal['rccm'] ?? '' }} — Contribuable N° : {{ $legal['contribuable'] ?? '' }} — Régime {{ $legal['regime'] ?? '' }}<br>
        Tél : {{ $legal['tel'] ?? '' }} — E-mail : {{ $legal['email'] ?? '' }}
    </div>
</body>
</html>
