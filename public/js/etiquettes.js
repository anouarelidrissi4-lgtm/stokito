/* Stokito · générateur de code barre gratuit (EAN-13, EAN-8, UPC-A, Code 128).
   Tout se passe dans le navigateur : aucun code n'est envoyé au serveur. 2026-11-25 */
(function (root) {
    'use strict';

    /* ---------- encodage ---------- */
    var L = ['0001101', '0011001', '0010011', '0111101', '0100011', '0110001', '0101111', '0111011', '0110111', '0001011'];
    var R = L.map(function (p) { return p.replace(/./g, function (b) { return b === '0' ? '1' : '0'; }); });
    var G = R.map(function (p) { return p.split('').reverse().join(''); });
    var PARITY = ['LLLLLL', 'LLGLGG', 'LLGGLG', 'LLGGGL', 'LGLLGG', 'LGGLLG', 'LGGGLL', 'LGLGLG', 'LGLGGL', 'LGGLGL'];

    var C128 = ['212222', '222122', '222221', '121223', '121322', '131222', '122213', '122312', '132212', '221213',
        '221312', '231212', '112232', '122132', '122231', '113222', '123122', '123221', '223211', '221132',
        '221231', '213212', '223112', '312131', '311222', '321122', '321221', '312212', '322112', '322211',
        '212123', '212321', '232121', '111323', '131123', '131321', '112313', '132113', '132311', '211313',
        '231113', '231311', '112133', '112331', '132131', '113123', '113321', '133121', '313121', '211331',
        '231131', '213113', '213311', '213131', '311123', '311321', '331121', '312113', '312311', '332111',
        '314111', '221411', '431111', '111224', '111422', '121124', '121421', '141122', '141221', '112214',
        '112412', '122114', '122411', '142112', '142211', '241211', '221114', '413111', '241112', '134111',
        '111242', '121142', '121241', '114212', '124112', '124211', '411212', '421112', '421211', '212141',
        '214121', '412121', '111143', '111341', '131141', '114113', '114311', '411113', '411311', '113141',
        '114131', '311141', '411131', '211412', '211214', '211232', '2331112'];

    function checkDigit(body) {                      // GTIN : poids 3,1,3… depuis la droite
        var s = 0;
        for (var i = body.length - 1, w = 3; i >= 0; i--, w = 4 - w) s += +body[i] * w;
        return String((10 - s % 10) % 10);
    }

    function widthsToBits(w) {
        var out = '';
        for (var i = 0; i < w.length; i++) out += (i % 2 ? '0' : '1').repeat(+w[i]);
        return out;
    }

    var TYPES = {
        ean13: { label: 'EAN-13', len: 13 },
        ean8: { label: 'EAN-8', len: 8 },
        upca: { label: 'UPC-A', len: 12 },
        code128: { label: 'Code 128' }
    };

    /* Prépare la saisie : ajoute la clé si elle manque, la vérifie sinon.
       Renvoie { ok, code, msg, level } (level : ok | info | error). */
    function prepare(type, raw) {
        var v = String(raw || '').trim();
        if (type === 'code128') {
            if (!v) return { ok: false, msg: 'Saisissez un texte ou un numéro.', level: 'error' };
            if (v.length > 48) return { ok: false, msg: '48 caractères au maximum.', level: 'error' };
            if (!/^[\x20-\x7e]+$/.test(v)) return { ok: false, msg: 'Lettres sans accent, chiffres et signes courants uniquement (pas d’accents ni d’emojis).', level: 'error' };
            return { ok: true, code: v, msg: v.length + ' caractère(s), Code 128 accepte lettres, chiffres et signes.', level: 'ok' };
        }
        var n = TYPES[type].len;
        v = v.replace(/[\s-]/g, '');
        if (!v) return { ok: false, msg: 'Saisissez ' + (n - 1) + ' ou ' + n + ' chiffres.', level: 'error' };
        if (!/^\d+$/.test(v)) return { ok: false, msg: 'Un ' + TYPES[type].label + ' ne contient que des chiffres.', level: 'error' };
        if (v.length === n - 1) {
            var c = checkDigit(v);
            return { ok: true, code: v + c, msg: 'Clé de contrôle calculée et ajoutée : ' + c + '.', level: 'info' };
        }
        if (v.length === n) {
            var good = checkDigit(v.slice(0, -1));
            if (good !== v.slice(-1)) return { ok: false, msg: 'Clé de contrôle incorrecte : le dernier chiffre devrait être ' + good + ' (' + v.slice(0, -1) + good + ').', level: 'error' };
            return { ok: true, code: v, msg: 'Clé de contrôle valide.', level: 'ok' };
        }
        return { ok: false, msg: 'Un ' + TYPES[type].label + ' a ' + n + ' chiffres (ou ' + (n - 1) + ' sans la clé) : vous en avez saisi ' + v.length + '.', level: 'error' };
    }

    /* Modèle géométrique en « modules » : bits, barres longues, textes, zones de silence. */
    function encode(type, code) {
        var bits, tall = {}, texts = [], ql, qr, i, d;
        function mark(a, b) { for (var k = a; k <= b; k++) tall[k] = true; }
        if (type === 'ean13' || type === 'upca') {
            var c13 = type === 'upca' ? '0' + code : code;
            var par = PARITY[+c13[0]];
            bits = '101';
            for (i = 1; i <= 6; i++) { d = +c13[i]; bits += par[i - 1] === 'L' ? L[d] : G[d]; }
            bits += '01010';
            for (i = 7; i <= 12; i++) bits += R[+c13[i]];
            bits += '101';
            mark(0, 2); mark(45, 49); mark(92, 94);
            if (type === 'ean13') {
                ql = 11; qr = 7;
                texts.push({ x: -4.5, s: c13[0] });
                for (i = 1; i <= 6; i++) texts.push({ x: 3 + (i - 1) * 7 + 3.5, s: c13[i] });
                for (i = 7; i <= 12; i++) texts.push({ x: 50 + (i - 7) * 7 + 3.5, s: c13[i] });
            } else {
                ql = 9; qr = 9;
                mark(3, 9); mark(85, 91);
                texts.push({ x: -4.5, s: code[0], small: true });
                for (i = 1; i <= 5; i++) texts.push({ x: 10 + (i - 1) * 7 + 3.5, s: code[i] });
                for (i = 6; i <= 10; i++) texts.push({ x: 50 + (i - 6) * 7 + 3.5, s: code[i] });
                texts.push({ x: 99.5, s: code[11], small: true });
            }
        } else if (type === 'ean8') {
            bits = '101';
            for (i = 0; i < 4; i++) bits += L[+code[i]];
            bits += '01010';
            for (i = 4; i < 8; i++) bits += R[+code[i]];
            bits += '101';
            mark(0, 2); mark(31, 35); mark(64, 66);
            ql = 7; qr = 7;
            for (i = 0; i < 4; i++) texts.push({ x: 3 + i * 7 + 3.5, s: code[i] });
            for (i = 4; i < 8; i++) texts.push({ x: 36 + (i - 4) * 7 + 3.5, s: code[i] });
        } else {
            var vals = [], start;
            if (/^\d+$/.test(code) && code.length >= 2) {
                start = 105;
                var even = code.length - code.length % 2;
                for (i = 0; i < even; i += 2) vals.push(+code.substr(i, 2));
                if (even < code.length) { vals.push(100); vals.push(code.charCodeAt(even) - 32); }
            } else {
                start = 104;
                for (i = 0; i < code.length; i++) vals.push(code.charCodeAt(i) - 32);
            }
            var sum = start;
            for (i = 0; i < vals.length; i++) sum += vals[i] * (i + 1);
            var all = [start].concat(vals, [sum % 103, 106]);
            bits = all.map(function (v) { return widthsToBits(C128[v]); }).join('');
            ql = 10; qr = 10;
            texts.push({ x: bits.length / 2, s: code, free: true });
        }
        return { type: type, code: code, bits: bits, tall: tall, texts: texts, ql: ql, qr: qr };
    }

    /* Géométrie commune au SVG et au PNG. */
    function layout(m, opt) {
        opt = opt || {};
        var h = opt.height || 60, text = opt.text !== false;
        var linear = m.type !== 'code128';
        var ext = linear && text ? 5 : 0;
        var fs = 9;
        var bars = [], i = 0;
        while (i < m.bits.length) {
            if (m.bits[i] === '1') {
                var j = i;
                while (j < m.bits.length && m.bits[j] === '1' && !!m.tall[j] === !!m.tall[i]) j++;
                bars.push({ x: m.ql + i, w: j - i, h: m.tall[i] ? h + ext : h });
                i = j;
            } else i++;
        }
        var W = m.ql + m.bits.length + m.qr;
        var H = text ? h + (linear ? fs + 2 : fs + 4) : h;
        var labels = !text ? [] : m.texts.map(function (t) {
            return { x: m.ql + t.x, y: h + (linear ? fs : fs + 2), s: t.s, size: t.small ? fs * 0.75 : fs };
        });
        if (text && !linear) {                       // texte libre : le réduire s'il dépasse
            var est = m.code.length * fs * 0.6;
            if (est > W - 4) labels[0].size = Math.max(4, fs * (W - 4) / est);
        }
        return { W: W, H: H, bars: bars, labels: labels, pad: 2 };
    }

    function esc(s) { return String(s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }

    function toSVG(m, opt) {
        opt = opt || {};
        var g = layout(m, opt), p = g.pad, scale = opt.scale || 2;
        var W = g.W, H = g.H + p * 2;
        var out = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' + W + ' ' + H + '"' +
            (opt.fluid ? ' preserveAspectRatio="xMidYMid meet" width="100%" height="100%"' : ' width="' + W * scale + '" height="' + H * scale + '"') +
            ' shape-rendering="crispEdges" role="img" aria-label="' + esc(TYPES[m.type].label + ' ' + m.code) + '">';
        out += '<rect width="' + W + '" height="' + H + '" fill="#fff"/><path fill="#000" d="';
        g.bars.forEach(function (b) { out += 'M' + b.x + ' ' + p + 'h' + b.w + 'v' + b.h + 'h-' + b.w + 'z'; });
        out += '"/>';
        if (g.labels.length) {
            out += '<g font-family="Arial,Helvetica,sans-serif" text-anchor="middle" fill="#000">';
            g.labels.forEach(function (t) { out += '<text x="' + t.x + '" y="' + (t.y + p) + '" font-size="' + t.size.toFixed(2) + '">' + esc(t.s) + '</text>'; });
            out += '</g>';
        }
        return out + '</svg>';
    }

    function toCanvas(m, opt, px) {
        var g = layout(m, opt), p = g.pad;
        px = px || 6;                                 // pixels par module
        var cv = document.createElement('canvas');
        cv.width = g.W * px; cv.height = (g.H + p * 2) * px;
        var c = cv.getContext('2d');
        c.fillStyle = '#fff'; c.fillRect(0, 0, cv.width, cv.height);
        c.fillStyle = '#000';
        g.bars.forEach(function (b) { c.fillRect(b.x * px, p * px, b.w * px, b.h * px); });
        c.textAlign = 'center'; c.textBaseline = 'alphabetic';
        g.labels.forEach(function (t) { c.font = (t.size * px) + 'px Arial, Helvetica, sans-serif'; c.fillText(t.s, t.x * px, (t.y + p) * px); });
        return cv;
    }

    /* ---------- préfixes GS1 (pays de l'organisation qui a attribué le code) ---------- */
    var GS1 = [
        [0, 19, 'États-Unis'], [20, 29, 'Usage interne (codes à diffusion restreinte d’un pays)'], [30, 39, 'États-Unis (médicaments)'],
        [40, 49, 'Usage interne (codes d’une entreprise)'], [50, 59, 'Réservé (GS1 États-Unis)'], [60, 139, 'États-Unis'],
        [200, 299, 'Usage interne (magasin, entrepôt, produits au poids)'], [300, 379, 'France et Monaco'], [380, 380, 'Bulgarie'],
        [381, 381, 'Kosovo'], [383, 383, 'Slovénie'], [385, 385, 'Croatie'], [387, 387, 'Bosnie-Herzégovine'], [389, 389, 'Monténégro'],
        [400, 440, 'Allemagne'], [450, 459, 'Japon'], [460, 469, 'Russie'], [470, 470, 'Kirghizistan'], [471, 471, 'Taïwan'],
        [474, 474, 'Estonie'], [475, 475, 'Lettonie'], [476, 476, 'Azerbaïdjan'], [477, 477, 'Lituanie'], [478, 478, 'Ouzbékistan'],
        [479, 479, 'Sri Lanka'], [480, 480, 'Philippines'], [481, 481, 'Biélorussie'], [482, 482, 'Ukraine'], [483, 483, 'Turkménistan'],
        [484, 484, 'Moldavie'], [485, 485, 'Arménie'], [486, 486, 'Géorgie'], [487, 487, 'Kazakhstan'], [488, 488, 'Tadjikistan'],
        [489, 489, 'Hong Kong'], [490, 499, 'Japon'], [500, 509, 'Royaume-Uni'], [520, 521, 'Grèce'], [528, 528, 'Liban'],
        [529, 529, 'Chypre'], [530, 530, 'Albanie'], [531, 531, 'Macédoine du Nord'], [535, 535, 'Malte'], [539, 539, 'Irlande'],
        [540, 549, 'Belgique et Luxembourg'], [560, 560, 'Portugal'], [569, 569, 'Islande'], [570, 579, 'Danemark, îles Féroé et Groenland'],
        [590, 590, 'Pologne'], [594, 594, 'Roumanie'], [599, 599, 'Hongrie'], [600, 601, 'Afrique du Sud'], [603, 603, 'Ghana'],
        [604, 604, 'Sénégal'], [605, 605, 'Ouganda'], [606, 606, 'Angola'], [607, 607, 'Oman'], [608, 608, 'Bahreïn'], [609, 609, 'Maurice'],
        [611, 611, 'Maroc'], [612, 612, 'Somalie'], [613, 613, 'Algérie'], [615, 615, 'Nigeria'], [616, 616, 'Kenya'], [617, 617, 'Cameroun'],
        [618, 618, 'Côte d’Ivoire'], [619, 619, 'Tunisie'], [620, 620, 'Tanzanie'], [621, 621, 'Syrie'], [622, 622, 'Égypte'],
        [623, 623, 'GS1 Global Office (ancien préfixe du Brunei)'], [624, 624, 'Libye'], [625, 625, 'Jordanie'], [626, 626, 'Iran'],
        [627, 627, 'Koweït'], [628, 628, 'Arabie saoudite'], [629, 629, 'Émirats arabes unis'], [630, 630, 'Qatar'], [631, 631, 'Namibie'],
        [632, 632, 'Rwanda'], [640, 649, 'Finlande'], [680, 681, 'Chine'], [690, 699, 'Chine'], [700, 709, 'Norvège'], [729, 729, 'Israël'],
        [730, 739, 'Suède'], [740, 740, 'Guatemala'], [741, 741, 'Salvador'], [742, 742, 'Honduras'], [743, 743, 'Nicaragua'],
        [744, 744, 'Costa Rica'], [745, 745, 'Panama'], [746, 746, 'République dominicaine'], [750, 750, 'Mexique'], [754, 755, 'Canada'],
        [759, 759, 'Venezuela'], [760, 769, 'Suisse et Liechtenstein'], [770, 771, 'Colombie'], [773, 773, 'Uruguay'], [775, 775, 'Pérou'],
        [777, 777, 'Bolivie'], [778, 779, 'Argentine'], [780, 780, 'Chili'], [784, 784, 'Paraguay'], [786, 786, 'Équateur'],
        [789, 790, 'Brésil'], [800, 839, 'Italie, Saint-Marin et Vatican'], [840, 849, 'Espagne et Andorre'], [850, 850, 'Cuba'],
        [858, 858, 'Slovaquie'], [859, 859, 'République tchèque'], [860, 860, 'Serbie'], [865, 865, 'Mongolie'], [867, 867, 'Corée du Nord'],
        [868, 869, 'Turquie'], [870, 879, 'Pays-Bas'], [880, 881, 'Corée du Sud'], [883, 883, 'Myanmar'], [884, 884, 'Cambodge'],
        [885, 885, 'Thaïlande'], [887, 887, 'Laos'], [888, 888, 'Singapour'], [890, 890, 'Inde'], [893, 893, 'Viêt Nam'],
        [894, 894, 'Bangladesh'], [896, 896, 'Pakistan'], [899, 899, 'Indonésie'], [900, 919, 'Autriche'], [930, 939, 'Australie'],
        [940, 949, 'Nouvelle-Zélande'], [950, 950, 'GS1 Global Office (pays sans organisation GS1)'], [951, 951, 'Codes EPC (identifiant GID)'],
        [952, 952, 'Exemples et démonstrations GS1'], [955, 955, 'Malaisie'], [958, 958, 'Macao'], [960, 969, 'Codes EAN-8 (GS1 Royaume-Uni, Pologne et Global Office)'],
        [977, 977, 'Presse et revues (ISSN)'], [978, 979, 'Livres (ISBN) et partitions (ISMN)'], [980, 980, 'Bons de remboursement'],
        [981, 983, 'Coupons (zones monétaires communes)'], [990, 999, 'Coupons']
    ];

    function country(code) {
        var d = String(code || '').replace(/\D/g, '');
        if (d.length === 12) d = '0' + d;            // UPC-A → EAN-13
        if (d.length < 3) return null;
        var p = +d.slice(0, 3);
        for (var i = 0; i < GS1.length; i++) if (p >= GS1[i][0] && p <= GS1[i][1]) return { prefix: d.slice(0, 3), name: GS1[i][2] };
        return { prefix: d.slice(0, 3), name: null };
    }

    /* Vérification d'un code saisi tel quel (8, 12, 13 ou 14 chiffres). */
    function verify(raw) {
        var v = String(raw || '').replace(/[\s-]/g, '');
        if (!v) return null;
        if (!/^\d+$/.test(v)) return { ok: false, msg: 'Un code EAN ou UPC ne contient que des chiffres. Les codes avec des lettres sont des Code 128 ou Code 39 : ils n’ont pas de clé de contrôle à vérifier ici.' };
        var kind = { 8: 'EAN-8', 12: 'UPC-A', 13: 'EAN-13', 14: 'GTIN-14 (ITF-14, cartons)' }[v.length];
        if (!kind) return { ok: false, msg: v.length + ' chiffres : ce n’est ni un EAN-8 (8), ni un UPC-A (12), ni un EAN-13 (13), ni un GTIN-14 (14).' };
        var good = checkDigit(v.slice(0, -1)), ok = good === v.slice(-1);
        return {
            ok: ok, kind: kind, good: good, fixed: v.slice(0, -1) + good,
            country: v.length === 8 ? null : country(v.length === 14 ? v.slice(1) : v)
        };
    }

    function randomInternal() {                      // préfixe 2 : réservé à l'usage interne
        var b = '2';
        for (var i = 0; i < 11; i++) b += Math.floor(Math.random() * 10);
        return b + checkDigit(b);
    }

    var FORMATS = {
        '3x8': { cols: 3, rows: 8, w: 70, h: 37, gx: 0, gy: 0 },
        '3x7': { cols: 3, rows: 7, w: 70, h: 42.3, gx: 0, gy: 0 },
        '4x10': { cols: 4, rows: 10, w: 52.5, h: 29.7, gx: 0, gy: 0 },
        '2x7': { cols: 2, rows: 7, w: 99.1, h: 38.1, gx: 2.5, gy: 0 },
        '5x13': { cols: 5, rows: 13, w: 38.1, h: 21.2, gx: 2.5, gy: 0 }
    };
    var SHEET_CSS =
        '.lbs-page{position:relative;width:210mm;height:297mm;background:#fff;overflow:hidden;box-sizing:border-box}' +
        '.lbs-lab{position:absolute;box-sizing:border-box;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.6mm;padding:2mm 2.5mm;font-family:Arial,Helvetica,sans-serif;color:#000;overflow:hidden;text-align:center}' +
        '.lbs-sm .lbs-lab{padding:1mm 1.2mm;gap:.3mm}' +
        '.lbs-nm{font-size:3mm;font-weight:700;line-height:1.15;max-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}' +
        '.lbs-sm .lbs-nm{font-size:2.2mm}' +
        '.lbs-bc{flex:1 1 auto;min-height:0;width:100%;display:flex;align-items:center;justify-content:center}' +
        '.lbs-bc svg{display:block;max-width:100%;max-height:100%;width:auto;height:100%}' +
        '.lbs-pr{font-size:3.6mm;font-weight:800;line-height:1.1}' +
        '.lbs-sm .lbs-pr{font-size:2.6mm}' +
        '.lbs-guide .lbs-lab{outline:.2mm dashed #d4d4d8;outline-offset:-.1mm}';

    /* Type d'un code déjà existant (code barre d'un produit) : EAN/UPC si la clé est juste, sinon Code 128. */
    function detect(raw) {
        var v = String(raw || '').trim();
        if (/^\d+$/.test(v) && (v.length === 13 || v.length === 8 || v.length === 12) && checkDigit(v.slice(0, -1)) === v.slice(-1)) {
            return { 13: 'ean13', 8: 'ean8', 12: 'upca' }[v.length];
        }
        if (v && v.length <= 48 && /^[\x20-\x7e]+$/.test(v)) return 'code128';
        return null;
    }

    /* Planche A4 : cells = [{type, code, name, price}], o = {format, start, guide, onlyFirst, text}. */
    function sheet(cells, o) {
        o = o || {};
        var f = FORMATS[o.format] || FORMATS['3x8'], per = f.cols * f.rows;
        var skip = Math.min(per - 1, Math.max(0, (parseInt(o.start, 10) || 1) - 1));
        var ml = (210 - f.cols * f.w - (f.cols - 1) * f.gx) / 2, mt = (297 - f.rows * f.h - (f.rows - 1) * f.gy) / 2;
        var cache = {}, html = [], all = [], i;
        for (i = 0; i < skip; i++) all.push(null);
        all = all.concat(cells);
        var total = Math.ceil(all.length / per), sm = f.h < 25 ? ' lbs-sm' : '';
        for (var pg = 0; pg < (o.onlyFirst ? Math.min(1, total) : total); pg++) {
            var h = '<div class="lbs-page' + sm + (o.guide ? ' lbs-guide' : '') + '">';
            for (i = 0; i < per; i++) {
                var c = all[pg * per + i], col = i % f.cols, row = Math.floor(i / f.cols);
                var pos = 'left:' + (ml + col * (f.w + f.gx)).toFixed(2) + 'mm;top:' + (mt + row * (f.h + f.gy)).toFixed(2) + 'mm;width:' + f.w + 'mm;height:' + f.h + 'mm';
                if (!c) { h += o.guide ? '<div class="lbs-lab" style="' + pos + '"></div>' : ''; continue; }
                var key = c.type + '|' + c.code;
                if (!cache[key]) cache[key] = toSVG(encode(c.type, c.code), { fluid: true, height: 40, text: o.text !== false });
                h += '<div class="lbs-lab" style="' + pos + '">' + (c.name ? '<div class="lbs-nm">' + esc(c.name) + '</div>' : '') +
                    '<div class="lbs-bc">' + cache[key] + '</div>' + (c.price ? '<div class="lbs-pr">' + esc(c.price) + '</div>' : '') + '</div>';
            }
            html.push(h + '</div>');
        }
        return { html: html, pages: total, per: per };
    }

    /* Ouvre les pages dans une fenêtre et lance l'impression. false si la fenêtre est bloquée. */
    function printSheet(pagesHtml, title) {
        var w = window.open('', '_blank');
        if (!w) return false;
        w.document.write('<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><title>' + esc(title || 'Étiquettes code barre') + '</title><style>@page{size:A4;margin:0}html,body{margin:0;padding:0;background:#fff}' +
            SHEET_CSS + '.lbs-page{page-break-after:always;break-after:page}.lbs-page:last-child{page-break-after:auto;break-after:auto}' +
            '@media screen{body{background:#e4e4e7}.lbs-page{margin:16px auto;box-shadow:0 2px 12px rgba(0,0,0,.15)}}</style></head><body>' +
            pagesHtml.join('') + '<script>window.onload=function(){setTimeout(function(){window.print()},250)}<\/script></body></html>');
        w.document.close();
        return true;
    }

    /* Aperçu réduit de la première page dans box (largeur du conteneur). */
    function preview(box, pageHtml) {
        if (!document.getElementById('lbs-style')) {
            var st = document.createElement('style'); st.id = 'lbs-style'; st.textContent = SHEET_CSS; document.head.appendChild(st);
        }
        var w = box.clientWidth || 300, s = w / (210 * 96 / 25.4);
        box.style.height = (297 * 96 / 25.4 * s) + 'px';
        box.innerHTML = '<div style="position:absolute;left:0;top:0;transform:scale(' + s + ');transform-origin:0 0">' + (pageHtml || '<div class="lbs-page lbs-guide"></div>') + '</div>';
    }

    var api = { TYPES: TYPES, checkDigit: checkDigit, prepare: prepare, encode: encode, toSVG: toSVG, toCanvas: toCanvas, country: country, verify: verify, randomInternal: randomInternal, GS1: GS1, FORMATS: FORMATS, SHEET_CSS: SHEET_CSS, detect: detect, sheet: sheet, printSheet: printSheet, preview: preview };
    if (typeof module !== 'undefined' && module.exports) module.exports = api;
    root.StokitoBarcode = api;
})(typeof window !== 'undefined' ? window : this);

/* Stokito · Produits › Étiquettes code barre (2026-11-26). Needs /js/code-barre.js (window.StokitoBarcode).
   Product data comes from <script type="application/json" id="lbl-data">. Nothing is saved: quantities live in the page. */
(function () {
    'use strict';
    function $(id) { return document.getElementById(id); }
    function esc(s) { return String(s == null ? '' : s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }
    function norm(s) { return String(s || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, ''); }

    function init() {
        var root = $('lbl-app');
        if (!root || !window.StokitoBarcode || root.getAttribute('data-ready')) return;
        root.setAttribute('data-ready', '1');
        var B = window.StokitoBarcode;
        var data = JSON.parse($('lbl-data').textContent);
        var cur = { EUR: '€', MAD: 'DH' }[String(data.currency || '').toUpperCase()] || data.currency || '';
        var nf = new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        var qty = {};                                   // product id -> number of labels

        var products = data.products.map(function (p) {
            var t = B.detect(p.barcode), code = p.barcode, src = 'barcode', warn = '';
            if (p.barcode && t === 'code128' && /^\d+$/.test(p.barcode) && [8, 12, 13].indexOf(p.barcode.length) >= 0) warn = 'Clé de contrôle incorrecte : imprimé en Code 128.';
            if (!t && p.barcode) warn = 'Code barre non imprimable (accents ou plus de 48 caractères).';
            if (!t && p.ref && B.detect(p.ref)) { t = B.detect(p.ref); code = p.ref; src = 'ref'; }
            if (!t) code = null;
            return { id: p.id, name: p.name, ref: p.ref, cat: p.cat, stock: p.stock, stockLabel: p.stockLabel, ttc: p.price, tva: p.tva, type: t, code: code, src: src, warn: warn, key: norm(p.name + ' ' + (p.ref || '') + ' ' + (p.barcode || '')) };
        });

        var search = $('lbl-search'), catSel = $('lbl-cat'), list = $('lbl-rows'), fmt = $('lbl-format'), start = $('lbl-start');
        var showName = $('lbl-name'), priceMode = $('lbl-price'), showDigits = $('lbl-digits'), msg = $('lbl-msg'), box = $('lbl-preview');

        function visible() {
            var q = norm(search.value.trim()), c = catSel.value;
            return products.filter(function (p) { return (!q || p.key.indexOf(q) >= 0) && (!c || String(p.cat) === c); });
        }
        function price(p) {
            if (priceMode.value === 'none' || p.ttc == null) return '';
            // Stokito stores the sale price TTC (form label "Prix de vente TTC")
            var v = priceMode.value === 'ttc' ? p.ttc : p.ttc / (1 + (p.tva == null ? data.tvaDefault : p.tva) / 100);
            return nf.format(v) + ' ' + cur + (priceMode.value === 'ht' ? ' HT' : '');
        }
        function cells() {
            var out = [];
            products.forEach(function (p) {
                var n = qty[p.id] || 0;
                for (var k = 0; k < n && p.code; k++) out.push({ type: p.type, code: p.code, name: showName.checked ? p.name : '', price: price(p) });
            });
            return out;
        }
        var TYPE = { ean13: 'EAN-13', ean8: 'EAN-8', upca: 'UPC-A', code128: 'Code 128' };

        function renderRows() {
            var v = visible();
            if (!v.length) { list.innerHTML = '<p class="px-4 py-10 text-center text-sm text-zinc-500">Aucun produit.</p>'; return; }
            list.innerHTML = v.map(function (p) {
                var codeInfo = p.code
                    ? '<span class="inline-flex items-center rounded bg-zinc-100 px-1.5 py-0.5 text-[11px] font-semibold text-zinc-700">' + TYPE[p.type] + '</span> <span class="font-mono text-xs text-zinc-600">' + esc(p.code) + '</span>' +
                      (p.src === 'ref' ? ' <span class="text-[11px] text-zinc-400">(référence, pas de code barre)</span>' : '') +
                      (p.warn ? ' <span class="text-[11px] text-red-600">' + esc(p.warn) + '</span>' : '')
                    : '<span class="text-xs text-red-600">Ni code barre ni référence imprimable</span>';
                return '<div class="flex items-center gap-3 px-4 py-3' + (p.code ? '' : ' opacity-60') + '">' +
                    '<div class="min-w-0 flex-1"><div class="truncate text-sm font-semibold text-zinc-900">' + esc(p.name) + '</div>' +
                    '<div class="mt-0.5 flex flex-wrap items-center gap-x-1.5 gap-y-0.5">' + codeInfo + '</div>' +
                    '<div class="mt-0.5 text-xs text-zinc-500">' + (price(p) ? esc(price(p)) + ' · ' : '') + 'Stock : ' + esc(p.stockLabel) + '</div></div>' +
                    (p.code ? '<div class="flex items-center rounded-lg border border-zinc-300 bg-white">' +
                        '<button type="button" data-step="-1" data-id="' + p.id + '" class="w-9 h-9 text-lg text-zinc-600 hover:text-zinc-900" aria-label="Moins">−</button>' +
                        '<input type="number" min="0" max="999" inputmode="numeric" data-id="' + p.id + '" value="' + (qty[p.id] || 0) + '" class="w-14 h-9 border-0 p-0 text-center text-sm font-semibold focus:ring-0" aria-label="Nombre d’étiquettes pour ' + esc(p.name) + '">' +
                        '<button type="button" data-step="1" data-id="' + p.id + '" class="w-9 h-9 text-lg text-zinc-600 hover:text-zinc-900" aria-label="Plus">+</button></div>' : '') +
                    '</div>';
            }).join('');
        }

        var timer;
        function renderSheet() {
            clearTimeout(timer);
            timer = setTimeout(function () {
                var c = cells();
                var r = B.sheet(c, { format: fmt.value, start: start.value, guide: true, onlyFirst: true, text: showDigits.checked });
                B.preview(box, r.html[0]);
                msg.textContent = c.length ? c.length + ' étiquette(s) sur ' + r.pages + ' page(s) A4' : 'Choisissez le nombre d’étiquettes de chaque produit.';
                $('lbl-count').textContent = c.length;
            }, 60);
        }
        function setQty(id, n) { qty[id] = Math.max(0, Math.min(999, parseInt(n, 10) || 0)); }

        list.addEventListener('click', function (e) {
            var b = e.target.closest('[data-step]');
            if (!b) return;
            var id = b.getAttribute('data-id');
            setQty(id, (qty[id] || 0) + (+b.getAttribute('data-step')));
            list.querySelector('input[data-id="' + id + '"]').value = qty[id];
            renderSheet();
        });
        list.addEventListener('input', function (e) {
            if (e.target.matches('input[data-id]')) { setQty(e.target.getAttribute('data-id'), e.target.value); renderSheet(); }
        });
        function bulk(fn) { visible().forEach(function (p) { if (p.code) setQty(p.id, fn(p)); }); renderRows(); renderSheet(); }
        $('lbl-one').addEventListener('click', function () { bulk(function () { return 1; }); });
        $('lbl-stock').addEventListener('click', function () { bulk(function (p) { return Math.max(0, Math.floor(+p.stock || 0)); }); });
        $('lbl-zero').addEventListener('click', function () { bulk(function () { return 0; }); });
        search.addEventListener('input', renderRows);
        catSel.addEventListener('change', renderRows);
        [fmt, start, showName, priceMode, showDigits].forEach(function (el) { el.addEventListener('change', function () { renderRows(); renderSheet(); }); });
        start.addEventListener('input', renderSheet);
        window.addEventListener('resize', renderSheet);

        $('lbl-print').addEventListener('click', function () {
            var c = cells();
            if (!c.length) { msg.textContent = 'Mettez au moins une étiquette.'; return; }
            var r = B.sheet(c, { format: fmt.value, start: start.value, text: showDigits.checked });
            if (!B.printSheet(r.html, 'Étiquettes · ' + (data.company || 'Stokito'))) msg.textContent = 'Autorisez les fenêtres pop-up pour imprimer.';
        });

        // ?ids=1,2,3 preselects products (one label each)
        (new URLSearchParams(location.search).get('ids') || '').split(',').forEach(function (id) {
            if (id && products.some(function (p) { return String(p.id) === id && p.code; })) qty[id] = 1;
        });
        renderRows();
        renderSheet();
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
    document.addEventListener('livewire:navigated', init);
})();
