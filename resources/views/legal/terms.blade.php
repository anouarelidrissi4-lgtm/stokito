{{-- CGU + CGV (2026-11-27): plans payants, prix lus dans App\Support\Plans (une seule source), paiement manuel WhatsApp/virement/PayPal,
     pas de renouvellement automatique, remboursement : 14 jours sur le premier paiement + erreurs de facturation. --}}
@php
    $P = \App\Support\Plans::class;
    $pro = $P::limits('pro');
    $prix = fn ($plan, $period) => $P::PRICES[$plan][$period]['MAD'].' DH ou '.$P::PRICES[$plan][$period]['EUR'].' €';
@endphp
<x-legal-page title="Conditions générales d’utilisation et de vente" ref="SP-CGU" updated="27/11/2026">
<h2>1. Le service</h2>
<p>Stokito est un logiciel en ligne qui permet de gérer un stock, des clients, des fournisseurs, des devis, des bons de livraison, des factures, des règlements et une caisse. Il s’utilise dans un navigateur, sans installation. Il est destiné aux professionnels : entreprises, commerçants, artisans, auto-entrepreneurs.</p>
<p>Ces conditions s’appliquent à toute personne qui crée un compte. En vous inscrivant, et en payant un plan le cas échéant, vous les acceptez.</p>

<h2>2. Les plans</h2>
<ul>
    <li><strong>Free</strong> (gratuit, sans limite de durée) : toutes les fonctions de gestion, avec {{ $P::FREE_INVOICES_PER_MONTH }} factures par mois. Les fonctions d’intelligence artificielle (Copilot, scanner de factures fournisseurs) ne sont pas incluses.</li>
    <li><strong>Pro</strong> : factures illimitées, Copilot ({{ $pro['copilot'] }} questions par mois) et scanner de factures ({{ $pro['scans'] }} scans par mois).</li>
    <li><strong>Premium</strong> : tout est illimité.</li>
</ul>
<p>Les quotas sont comptés par société et par mois calendaire ; ils repartent à zéro le 1er de chaque mois. Le détail de votre plan et de votre consommation est affiché dans Administration › Abonnement.</p>

<h2>3. Essai gratuit</h2>
<p>Chaque nouvelle société bénéficie du plan Premium gratuitement pendant {{ $P::TRIAL_MONTHS }} mois, sans carte bancaire et sans engagement. À la fin de l’essai, le compte passe automatiquement au plan Free : rien n’est facturé et vos données sont conservées.</p>

<h2>4. Prix</h2>
<ul>
    <li>Pro : {{ $prix('pro', 'trimestre') }} pour 3 mois, {{ $prix('pro', 'annee') }} pour 1 an.</li>
    <li>Premium : {{ $prix('premium', 'trimestre') }} pour 3 mois, {{ $prix('premium', 'annee') }} pour 1 an.</li>
</ul>
<p>Les prix en dirhams s’appliquent aux sociétés établies au Maroc, les prix en euros aux autres. Le prix à payer est celui affiché le jour de votre commande. Si nos prix changent, nous l’annonçons au moins 30 jours à l’avance ; le changement ne s’applique jamais à une période déjà payée.</p>

<h2>5. Paiement et activation</h2>
<ul>
    <li>Choisissez votre plan dans Administration › Abonnement. Votre demande nous parvient sur WhatsApp et nous vous envoyons les coordonnées pour payer par virement ou versement bancaire.</li>
    <li>Vous pouvez aussi payer avec PayPal (montant en euros), en choisissant « Biens et services », puis nous envoyer la confirmation sur WhatsApp.</li>
    <li>Le plan est activé dès réception du paiement, en général dans la journée. Sa durée (3 mois ou 1 an) court à partir de l’activation ; si vous renouvelez le même plan avant sa fin, la nouvelle période s’ajoute à la fin de la période en cours.</li>
    <li>Un reçu de paiement vous est envoyé sur demande.</li>
</ul>

<h2>6. Fin d’un plan, pas de renouvellement automatique</h2>
<p>Un plan payé n’est jamais renouvelé ni prélevé automatiquement. Sa date de fin est indiquée dans Administration › Abonnement. Si vous ne renouvelez pas, votre compte passe au plan Free : vos produits, clients, documents et historiques restent accessibles et exportables ; seules les limites du plan Free s’appliquent (nombre de factures par mois, fonctions d’intelligence artificielle).</p>

<h2>7. Remboursement</h2>
<ul>
    <li><strong>Premier paiement</strong> : si Stokito ne vous convient pas, demandez le remboursement dans les 14 jours qui suivent l’activation de votre premier plan payé. Il est remboursé en totalité, par le même moyen de paiement, dans les 14 jours suivant votre demande.</li>
    <li><strong>Erreur</strong> : un paiement en double ou un montant erroné est toujours remboursé.</li>
    <li><strong>Arrêt du service</strong> : si nous cessons de fournir Stokito, ou si le service est indisponible plus de 7 jours d’affilée par notre faute, la partie non utilisée de votre période payée vous est remboursée.</li>
    <li>En dehors de ces cas, une période commencée n’est pas remboursée.</li>
</ul>
<p>Pour toute demande, écrivez-nous sur WhatsApp ou à contact@stokito.com en indiquant le nom de votre société.</p>

<h2>8. Votre compte</h2>
<ul>
    <li>Vous fournissez des informations exactes lors de l’inscription.</li>
    <li>Vous gardez votre mot de passe secret et vous êtes responsable de ce qui est fait avec votre compte et ceux de vos employés.</li>
    <li>Vous pouvez exporter vos données et demander la suppression de votre compte à tout moment. La suppression d’un compte ne donne pas droit à remboursement, sauf dans les cas prévus à l’article 7.</li>
</ul>

<h2>9. Vos documents</h2>
<p>Vous êtes responsable du contenu de vos devis, factures et autres documents, et de leur conformité aux règles de votre pays (mentions obligatoires, taux de TVA, conservation, facturation électronique lorsqu’elle est obligatoire). Stokito vous aide à les préparer, mais ne remplace pas votre comptable. La caisse Stokito n’est pas certifiée NF525 (voir les <a href="{{ route('legal.mentions') }}">mentions légales</a>).</p>

<h2>10. Disponibilité</h2>
<p>Nous faisons notre possible pour que le service soit disponible et que les données soient sauvegardées chaque nuit. Des interruptions courtes peuvent arriver pour maintenance ou en cas d’incident. Pensez à exporter régulièrement vos données importantes.</p>

<h2>11. Responsabilité</h2>
<p>Stokito est fourni avec soin, mais sans garantie qu’il soit exempt de toute erreur. Sauf faute grave de notre part, notre responsabilité est limitée au montant que vous avez payé pour Stokito au cours des 12 derniers mois. Nous ne sommes pas responsables des pertes indirectes (manque à gagner, perte de clientèle).</p>

<h2>12. Usage interdit et suspension</h2>
<p>Il est interdit d’utiliser Stokito pour des activités illégales, d’essayer d’accéder aux données d’autres utilisateurs ou de perturber le service. Un compte qui ne respecte pas ces règles peut être suspendu, après un avertissement sauf urgence.</p>

<h2>13. Données personnelles</h2>
<p>Le traitement de vos données est décrit dans notre <a href="{{ route('legal.privacy') }}">politique de confidentialité</a>.</p>

<h2>14. Modification des conditions</h2>
<p>Nous pouvons faire évoluer ces conditions. Les changements importants vous sont annoncés au moins 30 jours à l’avance ; la date de la dernière mise à jour figure en haut de cette page.</p>

<h2>15. Droit applicable et litiges</h2>
<p>Ces conditions sont soumises au droit marocain. En cas de désaccord, contactez-nous d’abord : nous cherchons toujours une solution amiable. Si vous résidez dans un autre pays, vous gardez les protections que la loi de votre pays rend obligatoires.</p>

<h2>16. Contact</h2>
<p>Stokito est édité par Anouar Elidrissi (voir les <a href="{{ route('legal.mentions') }}">mentions légales</a>). Pour toute question : <a href="mailto:contact@stokito.com">contact@stokito.com</a> ou WhatsApp <a href="https://wa.me/212676035777">+212 676 035 777</a>.</p>
</x-legal-page>
