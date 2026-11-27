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

/* ---------- interface de la page /scanner-code-barre-en-ligne (2026-11-27) ----------
   Lecture : BarcodeDetector natif si le navigateur l'a (Chrome Android, macOS…), sinon ZXing
   (/js/zxing-0.21.3.min.js, chargé seulement au premier scan). Rien n'est envoyé au serveur. */
(function () {
    'use strict';
    if (typeof document === 'undefined') return;
    var B = window.StokitoBarcode;
    var ZX_URL = '/js/zxing-0.21.3.min.js';
    var STORE = 'stk_scan_history';

    var NAMES = {
        EAN13: 'EAN-13', EAN8: 'EAN-8', UPCA: 'UPC-A', UPCE: 'UPC-E', CODE128: 'Code 128', CODE39: 'Code 39', CODE93: 'Code 93',
        ITF: 'ITF (2 parmi 5 entrelacé)', CODABAR: 'Codabar', QRCODE: 'QR code', DATAMATRIX: 'Data Matrix', PDF417: 'PDF417',
        AZTEC: 'Aztec', RSS14: 'GS1 DataBar', RSSEXPANDED: 'GS1 DataBar Expanded', DOUCHETTE: 'Lu par la douchette'
    };
    var NATIVE_FORMATS = ['ean_13', 'ean_8', 'upc_a', 'upc_e', 'code_128', 'code_39', 'code_93', 'itf', 'codabar', 'qr_code', 'data_matrix', 'pdf417', 'aztec'];

    function $(id) { return document.getElementById(id); }
    function key(f) { return String(f || '').toUpperCase().replace(/[^A-Z0-9]/g, ''); }
    function esc(s) { return String(s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
    function store(fn) { try { return fn(window.localStorage); } catch (e) { return null; } }

    /* ----- moteurs de lecture ----- */
    var nativeP = null, zxP = null, zxReader = null;
    function nativeDetector() {
        if (nativeP) return nativeP;
        nativeP = (async function () {
            if (!('BarcodeDetector' in window)) return null;
            try {
                var sup = await window.BarcodeDetector.getSupportedFormats();
                var f = NATIVE_FORMATS.filter(function (x) { return sup.indexOf(x) >= 0; });
                if (f.indexOf('ean_13') < 0 || f.indexOf('code_128') < 0) return null;
                return new window.BarcodeDetector({ formats: f });
            } catch (e) { return null; }
        })();
        return nativeP;
    }
    function loadZX() {
        if (window.ZXing) return Promise.resolve(window.ZXing);
        if (zxP) return zxP;
        zxP = new Promise(function (ok, ko) {
            var s = document.createElement('script');
            s.src = ZX_URL; s.async = true;
            s.onload = function () { window.ZXing ? ok(window.ZXing) : ko(new Error('zxing')); };
            s.onerror = function () { zxP = null; ko(new Error('zxing')); };
            document.head.appendChild(s);
        });
        return zxP;
    }
    function zxDecode(canvas, hard) {
        var Z = window.ZXing;
        if (!zxReader) zxReader = new Z.MultiFormatReader();
        var F = Z.BarcodeFormat, hints = new Map();
        hints.set(Z.DecodeHintType.POSSIBLE_FORMATS, [F.EAN_13, F.EAN_8, F.UPC_A, F.UPC_E, F.CODE_128, F.CODE_39, F.CODE_93, F.ITF,
            F.CODABAR, F.QR_CODE, F.DATA_MATRIX, F.PDF_417, F.AZTEC, F.RSS_14].filter(function (x) { return x !== undefined; }));
        if (hard) hints.set(Z.DecodeHintType.TRY_HARDER, true);
        try {
            var bmp = new Z.BinaryBitmap(new Z.HybridBinarizer(new Z.HTMLCanvasElementLuminanceSource(canvas)));
            var r = zxReader.decode(bmp, hints);
            return { text: r.getText(), format: key(F[r.getBarcodeFormat()]) };
        } catch (e) {
            return null;
        } finally {
            try { zxReader.reset(); } catch (e2) { /* rien */ }
        }
    }
    /* Image ou frame vidéo → canvas réduit (les gros fichiers de téléphone ralentissent ZXing). */
    function toCanvas(src, w, h, max) {
        var k = Math.min(1, max / Math.max(w, h)), c = document.createElement('canvas');
        c.width = Math.max(1, Math.round(w * k)); c.height = Math.max(1, Math.round(h * k));
        var ctx = c.getContext('2d', { willReadFrequently: true });
        ctx.drawImage(src, 0, 0, c.width, c.height);
        return c;
    }
    async function decodeImage(img) {
        var det = await nativeDetector();
        if (det) {
            try {
                var n = await det.detect(img);
                if (n.length) return { text: n[0].rawValue, format: key(n[0].format) };
            } catch (e) { /* on essaie ZXing */ }
        }
        await loadZX();
        var w = img.naturalWidth || img.width, h = img.naturalHeight || img.height;
        /* photo de travers : ZXing lit les codes 1D à ±10° environ, on redresse par pas de 15° */
        var tries = [[1600, 0], [900, 0], [1600, -15], [1600, 15], [1600, -30], [1600, 30], [1600, -45], [1600, 45], [2400, 0]];
        for (var i = 0; i < tries.length; i++) {
            var c = tries[i][1] ? rotated(img, w, h, tries[i][0], tries[i][1]) : toCanvas(img, w, h, tries[i][0]);
            var r = zxDecode(c, true);
            if (r) return r;
            if (i % 2) await new Promise(function (ok) { setTimeout(ok, 0); });   // laisse respirer la page
        }
        return null;
    }
    function rotated(img, w, h, max, deg) {
        var k = Math.min(1, max / Math.max(w, h)), a = deg * Math.PI / 180, sw = w * k, sh = h * k;
        var cw = Math.abs(sw * Math.cos(a)) + Math.abs(sh * Math.sin(a)), ch = Math.abs(sw * Math.sin(a)) + Math.abs(sh * Math.cos(a));
        var c = document.createElement('canvas');
        c.width = Math.round(cw); c.height = Math.round(ch);
        var x = c.getContext('2d', { willReadFrequently: true });
        x.fillStyle = '#fff'; x.fillRect(0, 0, c.width, c.height);
        x.translate(c.width / 2, c.height / 2); x.rotate(a);
        x.drawImage(img, -sw / 2, -sh / 2, sw, sh);
        return c;
    }

    /* ----- ce que contient le code ----- */
    function describe(text, format) {
        var d = { text: text, format: format, name: NAMES[format] || format || 'Code barre', lines: [], level: 'ok', gtin: false, label: null };
        var digits = /^\d+$/.test(text);
        if (['EAN13', 'EAN8', 'UPCA', 'DOUCHETTE', 'ITF', 'RSS14'].indexOf(format) >= 0 && digits && [8, 12, 13, 14].indexOf(text.length) >= 0) {
            var v = B.verify(text);
            if (v && v.kind) {
                d.gtin = true;
                if (format === 'DOUCHETTE' || format === 'ITF' || format === 'RSS14') d.name = v.kind;
                d.lines.push(v.ok ? 'Clé de contrôle ' + v.good + ' : <b>code valide</b>.' : 'Clé de contrôle incorrecte (attendue : ' + v.good + '). Le code a peut-être été mal lu ou mal tapé.');
                if (!v.ok) d.level = 'error';
                if (v.country) d.lines.push('Préfixe <b class="font-mono">' + v.country.prefix + '</b> : ' + esc(v.country.name || 'non attribué') + (v.country.name && !/^Usage|Codes|Coupons|Livres|Presse|Bons|Réservé|GS1|Exemples/.test(v.country.name) ? ' <span class="text-zinc-500">(pays où la marque a obtenu ses codes)</span>' : '') + '.');
                var t = { 13: 'ean13', 8: 'ean8', 12: 'upca' }[text.length];
                if (t && v.ok) d.label = { type: t, code: text };
            }
        } else if (format === 'UPCE') {
            d.gtin = true;
            d.lines.push('Version courte d’un code UPC américain (produits de petite taille).');
        } else if (format === 'QRCODE' || format === 'DATAMATRIX' || format === 'AZTEC' || format === 'PDF417') {
            d.lines.push(text.length + ' caractère' + (text.length > 1 ? 's' : '') + '.');
        } else if (format === 'DOUCHETTE') {
            d.name = 'Code lu par la douchette';
        }
        if (/^https?:\/\/\S+$/i.test(text)) d.url = text;
        if (!d.label && !d.gtin && /^[\x20-\x7e]{1,48}$/.test(text) && !d.url) d.label = { type: 'code128', code: text };
        return d;
    }

    function init() {
        var app = $('sc-app');
        if (!app || !B) return;
        var video = $('sc-video'), status = $('sc-status'), startBtn = $('sc-start'), stopBtn = $('sc-stop');
        var camSel = $('sc-cams'), torchBtn = $('sc-torch'), multi = $('sc-multi');
        var out = $('sc-result'), hist = $('sc-history'), count = $('sc-count');
        var state = { stream: null, timer: null, det: null, busy: false, last: '', lastAt: 0, tab: 'camera', torch: false };
        var history = store(function (s) { return JSON.parse(s.getItem(STORE) || '[]'); }) || [];
        if (!Array.isArray(history)) history = [];

        function say(t, level) { status.textContent = t; status.setAttribute('data-level', level || 'info'); }

        /* ----- onglets ----- */
        function setTab(t) {
            state.tab = t;
            Array.prototype.forEach.call(app.querySelectorAll('[data-sc-tab]'), function (b) {
                b.setAttribute('aria-checked', b.getAttribute('data-sc-tab') === t ? 'true' : 'false');
            });
            Array.prototype.forEach.call(app.querySelectorAll('[data-sc-panel]'), function (p) {
                p.hidden = p.getAttribute('data-sc-panel') !== t;
            });
            if (t !== 'camera') stop();
            if (t === 'douchette') setTimeout(function () { $('sc-wedge').focus(); }, 50);
        }
        Array.prototype.forEach.call(app.querySelectorAll('[data-sc-tab]'), function (b) {
            b.addEventListener('click', function () { setTab(b.getAttribute('data-sc-tab')); });
        });

        /* ----- son + vibration ----- */
        var audio = null;
        function beep() {
            try {
                audio = audio || new (window.AudioContext || window.webkitAudioContext)();
                var o = audio.createOscillator(), g = audio.createGain();
                o.type = 'square'; o.frequency.value = 1800; g.gain.value = 0.06;
                o.connect(g); g.connect(audio.destination); o.start(); o.stop(audio.currentTime + 0.09);
            } catch (e) { /* pas de son */ }
            if (navigator.vibrate) try { navigator.vibrate(60); } catch (e) { /* rien */ }
        }

        /* ----- résultat + historique ----- */
        function show(d) {
            var acts = '<button type="button" data-sc-copy="' + esc(d.text) + '" class="inline-flex items-center justify-center h-11 px-4 rounded-lg bg-zinc-900 text-white text-sm font-semibold hover:bg-zinc-700">Copier</button>';
            if (d.gtin) {
                acts += '<a href="https://www.google.com/search?q=' + encodeURIComponent(d.text) + '" target="_blank" rel="noopener nofollow" class="inline-flex items-center justify-center h-11 px-4 rounded-lg border border-zinc-300 bg-white text-sm font-semibold hover:border-zinc-900">Rechercher le produit</a>';
                acts += '<a href="https://fr.openfoodfacts.org/produit/' + encodeURIComponent(d.text) + '" target="_blank" rel="noopener nofollow" class="inline-flex items-center justify-center h-11 px-4 rounded-lg border border-zinc-300 bg-white text-sm font-semibold hover:border-zinc-900">Open Food Facts</a>';
            }
            if (d.url) acts += '<a href="' + esc(d.url) + '" target="_blank" rel="noopener nofollow noreferrer" class="inline-flex items-center justify-center h-11 px-4 rounded-lg border border-zinc-300 bg-white text-sm font-semibold hover:border-zinc-900">Ouvrir le lien</a>';
            if (d.label) acts += '<a href="/generateur-code-barre?type=' + d.label.type + '&code=' + encodeURIComponent(d.label.code) + '#cb-app" class="inline-flex items-center justify-center h-11 px-4 rounded-lg bg-[#ffcc00] text-zinc-900 text-sm font-semibold hover:bg-[#ffd633]">Créer l’étiquette</a>';
            out.innerHTML =
                '<p class="text-xs font-semibold text-zinc-500">' + esc(d.name) + '</p>' +
                '<p class="mt-1 font-mono text-2xl sm:text-[28px] font-bold text-zinc-900 break-all leading-tight">' + esc(d.text.length > 300 ? d.text.slice(0, 300) + '…' : d.text) + '</p>' +
                (d.lines.length ? '<p class="mt-2 text-sm leading-relaxed ' + (d.level === 'error' ? 'text-red-600' : 'text-zinc-700') + '">' + d.lines.join('<br>') + '</p>' : '') +
                (d.url ? '<p class="mt-2 text-xs text-zinc-500">Lien contenu dans le code : vérifiez l’adresse avant de l’ouvrir.</p>' : '') +
                '<div class="mt-4 flex flex-wrap gap-2">' + acts + '</div>';
            out.setAttribute('data-filled', '1');
        }
        function save() { store(function (s) { s.setItem(STORE, JSON.stringify(history.slice(0, 500))); }); }
        function renderHistory() {
            count.textContent = history.length ? history.reduce(function (n, h) { return n + h.n; }, 0) + ' scan' + (history.length > 1 || history[0].n > 1 ? 's' : '') + ' · ' + history.length + ' code' + (history.length > 1 ? 's' : '') + ' différent' + (history.length > 1 ? 's' : '') : '';
            app.setAttribute('data-history', history.length ? '1' : '0');
            hist.innerHTML = history.slice(0, 200).map(function (h, i) {
                return '<li class="flex items-center gap-3 py-2">' +
                    '<span class="flex-1 min-w-0"><span class="block font-mono text-[15px] text-zinc-900 truncate">' + esc(h.text) + '</span><span class="block text-xs text-zinc-500">' + esc(NAMES[h.format] || h.format) + ' · ' + esc(h.time) + '</span></span>' +
                    '<span class="shrink-0 min-w-[2.25rem] h-7 px-2 grid place-items-center rounded-full bg-zinc-100 text-sm font-semibold text-zinc-900" title="Nombre de scans">×' + h.n + '</span>' +
                    '<button type="button" data-sc-del="' + i + '" class="shrink-0 w-8 h-8 grid place-items-center rounded-lg text-zinc-400 hover:text-red-600 hover:bg-zinc-100" aria-label="Retirer ce code">×</button></li>';
            }).join('');
        }
        function found(text, format) {
            text = String(text || '').replace(/[\r\n\t]+$/, '');
            if (!text) return;
            var now = Date.now();
            /* même code tenu devant la caméra : compté une seule fois, tant qu'il n'a pas quitté le cadre ~1,5 s */
            if (text === state.last && now - state.lastAt < 1500) { state.lastAt = now; return; }
            state.last = text; state.lastAt = now;
            beep();
            show(describe(text, format));
            var t = new Date(), time = String(t.getHours()).padStart(2, '0') + ':' + String(t.getMinutes()).padStart(2, '0') + ':' + String(t.getSeconds()).padStart(2, '0');
            var ex = history.findIndex(function (h) { return h.text === text; });
            if (ex >= 0) { var h = history.splice(ex, 1)[0]; h.n++; h.time = time; history.unshift(h); }
            else history.unshift({ text: text, format: format, n: 1, time: time, date: t.toISOString().slice(0, 10) });
            save(); renderHistory();
            if (state.stream && !multi.checked) { stop(); say('Code lu. Cliquez sur « Scanner » pour lire le suivant.', 'ok'); }
            else if (state.stream) say('Code lu. Présentez le code suivant.', 'ok');
            /* téléphone : le résultat est sous l'outil */
            if (!state.stream && state.tab !== 'douchette' && window.innerWidth < 1024) {
                var top = out.getBoundingClientRect().top;
                if (top > window.innerHeight - 120) out.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        app.addEventListener('click', function (e) {
            var c = e.target.closest('[data-sc-copy]'), del = e.target.closest('[data-sc-del]');
            if (c) copy(c.getAttribute('data-sc-copy'), c);
            if (del) { history.splice(+del.getAttribute('data-sc-del'), 1); save(); renderHistory(); }
        });
        function copy(text, btn) {
            var done = function () { if (btn) { var o = btn.textContent; btn.textContent = 'Copié ✓'; setTimeout(function () { btn.textContent = o; }, 1400); } };
            if (navigator.clipboard && window.isSecureContext) navigator.clipboard.writeText(text).then(done, function () { fallback(); });
            else fallback();
            function fallback() {
                var t = document.createElement('textarea'); t.value = text; t.style.position = 'fixed'; t.style.opacity = '0';
                document.body.appendChild(t); t.select();
                try { document.execCommand('copy'); done(); } catch (e) { /* rien */ }
                t.remove();
            }
        }
        $('sc-copy-all').addEventListener('click', function () { copy(history.map(function (h) { return h.text; }).join('\n'), this); });
        $('sc-csv').addEventListener('click', function () {
            var q = function (s) { s = String(s); return /[";\n]/.test(s) ? '"' + s.replace(/"/g, '""') + '"' : s; };
            var rows = [['Code', 'Type', 'Quantité', 'Date', 'Heure']].concat(history.slice().reverse().map(function (h) {
                return [h.text, NAMES[h.format] || h.format, h.n, h.date || '', h.time];
            }));
            var blob = new Blob(['﻿' + rows.map(function (r) { return r.map(q).join(';'); }).join('\r\n')], { type: 'text/csv;charset=utf-8' });
            var a = document.createElement('a');
            a.href = URL.createObjectURL(blob); a.download = 'scans-code-barre-' + new Date().toISOString().slice(0, 10) + '.csv';
            document.body.appendChild(a); a.click(); a.remove();
            setTimeout(function () { URL.revokeObjectURL(a.href); }, 2000);
        });
        $('sc-clear').addEventListener('click', function () {
            if (!history.length || !window.confirm('Effacer la liste des codes scannés ?')) return;
            history = []; state.last = ''; save(); renderHistory();
        });

        /* ----- caméra ----- */
        async function cameras() {
            try {
                var l = (await navigator.mediaDevices.enumerateDevices()).filter(function (d) { return d.kind === 'videoinput'; });
                camSel.innerHTML = l.map(function (d, i) { return '<option value="' + esc(d.deviceId) + '">' + esc(d.label || 'Caméra ' + (i + 1)) + '</option>'; }).join('');
                camSel.hidden = l.length < 2;
                var cur = state.stream && state.stream.getVideoTracks()[0];
                var id = cur && cur.getSettings ? cur.getSettings().deviceId : null;
                if (id) camSel.value = id;
            } catch (e) { camSel.hidden = true; }
        }
        async function start(deviceId) {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                say('Ce navigateur ne donne pas accès à la caméra. Utilisez l’onglet « Photo » ou ouvrez la page dans Chrome, Safari ou Firefox.', 'error');
                return;
            }
            stop(true);
            say('Autorisez l’accès à la caméra dans la fenêtre du navigateur…', 'info');
            startBtn.disabled = true;
            try {
                var v = deviceId ? { deviceId: { exact: deviceId } } : { facingMode: { ideal: 'environment' } };
                v.width = { ideal: 1280 }; v.height = { ideal: 720 };
                state.stream = await navigator.mediaDevices.getUserMedia({ video: v, audio: false });
            } catch (e) {
                startBtn.disabled = false;
                var n = e && e.name;
                say(n === 'NotAllowedError' || n === 'SecurityError' ? 'Accès à la caméra refusé. Autorisez la caméra pour ce site (icône à gauche de l’adresse), puis réessayez.'
                    : n === 'NotFoundError' || n === 'OverconstrainedError' ? 'Aucune caméra trouvée. Essayez l’onglet « Photo » ou « Douchette ».'
                    : n === 'NotReadableError' ? 'La caméra est déjà utilisée par une autre application. Fermez-la, puis réessayez.'
                    : 'Impossible d’ouvrir la caméra. Essayez l’onglet « Photo ».', 'error');
                return;
            }
            video.srcObject = state.stream;
            video.setAttribute('playsinline', ''); video.muted = true;
            try { await video.play(); } catch (e) { /* lecture auto bloquée : rare avec muted */ }
            app.setAttribute('data-live', '1');
            startBtn.disabled = false;
            cameras();
            var track = state.stream.getVideoTracks()[0], caps = track && track.getCapabilities ? track.getCapabilities() : {};
            torchBtn.hidden = !caps.torch; state.torch = false; torchBtn.setAttribute('aria-pressed', 'false');
            state.det = await nativeDetector();
            if (!state.det) {
                say('Chargement du lecteur…', 'info');
                try { await loadZX(); } catch (e) { say('Le lecteur n’a pas pu être chargé. Vérifiez votre connexion et réessayez.', 'error'); stop(); return; }
            }
            if (!state.stream) return;
            say('Placez le code barre dans le cadre, à 10-20 cm de la caméra.', 'info');
            loop();
        }
        var frame = null;
        async function tick() {
            if (!state.stream || video.readyState < 2 || !video.videoWidth) return;
            if (state.det) {
                var n = await state.det.detect(video);
                if (n.length) found(n[0].rawValue, key(n[0].format));
            } else {
                frame = toCanvas(video, video.videoWidth, video.videoHeight, 960);
                var r = zxDecode(frame, false);
                if (!r) return;
                /* image vidéo floue : un code 1D peut être mal lu une fois ; on attend 2 lectures identiques */
                if (/^(QRCODE|DATAMATRIX|AZTEC|PDF417)$/.test(r.format) || (state.cand === r.text && Date.now() - state.candAt < 1500)) { state.cand = ''; found(r.text, r.format); }
                else { state.cand = r.text; state.candAt = Date.now(); }
            }
        }
        function loop() {
            if (!state.stream) return;
            state.timer = setTimeout(async function () {
                if (state.busy) return loop();
                state.busy = true;
                try { await tick(); } catch (e) { /* frame illisible */ }
                state.busy = false;
                loop();
            }, state.det ? 90 : 140);
        }
        function stop(silent) {
            clearTimeout(state.timer); state.timer = null;
            if (state.stream) state.stream.getTracks().forEach(function (t) { t.stop(); });
            state.stream = null;
            video.srcObject = null;
            app.setAttribute('data-live', '0');
            torchBtn.hidden = true;
            if (!silent) say('Caméra arrêtée.', 'info');
        }
        startBtn.addEventListener('click', function () { start(camSel.hidden ? null : camSel.value || null); });
        stopBtn.addEventListener('click', function () { stop(); });
        camSel.addEventListener('change', function () { if (state.stream) start(camSel.value); });
        torchBtn.addEventListener('click', async function () {
            var track = state.stream && state.stream.getVideoTracks()[0];
            if (!track) return;
            try { state.torch = !state.torch; await track.applyConstraints({ advanced: [{ torch: state.torch }] }); torchBtn.setAttribute('aria-pressed', state.torch ? 'true' : 'false'); }
            catch (e) { torchBtn.hidden = true; }
        });
        document.addEventListener('visibilitychange', function () { if (document.hidden && state.stream) stop(); });

        /* ----- photo ----- */
        var file = $('sc-file'), drop = $('sc-drop'), thumb = $('sc-thumb');
        async function readFile(f) {
            if (!f || !/^image\//.test(f.type)) { say('Choisissez une image (JPG, PNG, WebP…).', 'error'); return; }
            say('Lecture de l’image…', 'info');
            var url = URL.createObjectURL(f), img = new Image();
            img.onload = async function () {
                thumb.src = url; thumb.hidden = false;
                try {
                    var r = await decodeImage(img);
                    if (r) { state.last = ''; found(r.text, r.format); say('Code lu sur l’image.', 'ok'); }
                    else say('Aucun code barre trouvé. Prenez une photo plus nette, bien droite, avec le code barre en entier et plus grand dans l’image.', 'error');
                } catch (e) { say('Le lecteur n’a pas pu être chargé. Vérifiez votre connexion et réessayez.', 'error'); }
            };
            img.onerror = function () { say('Cette image ne peut pas être ouverte par le navigateur (format HEIC ?). Essayez une capture d’écran ou un JPG.', 'error'); };
            img.src = url;
        }
        file.addEventListener('change', function () { readFile(file.files[0]); file.value = ''; });
        ['dragenter', 'dragover'].forEach(function (ev) { drop.addEventListener(ev, function (e) { e.preventDefault(); drop.setAttribute('data-over', '1'); }); });
        ['dragleave', 'drop'].forEach(function (ev) { drop.addEventListener(ev, function (e) { e.preventDefault(); drop.removeAttribute('data-over'); }); });
        drop.addEventListener('drop', function (e) { if (e.dataTransfer.files[0]) readFile(e.dataTransfer.files[0]); });
        document.addEventListener('paste', function (e) {
            if (state.tab !== 'image') return;
            var it = Array.prototype.find.call((e.clipboardData && e.clipboardData.items) || [], function (i) { return /^image\//.test(i.type); });
            if (it) readFile(it.getAsFile());
        });

        /* ----- douchette (lecteur USB / Bluetooth = clavier) ----- */
        var wedge = $('sc-wedge');
        wedge.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === 'Tab') {
                if (!wedge.value.trim()) return;
                e.preventDefault();
                state.last = '';
                found(wedge.value.trim(), 'DOUCHETTE');
                say('Code reçu de la douchette. Elle fonctionne.', 'ok');
                wedge.value = '';
            }
        });

        renderHistory();
        setTab('camera');
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
