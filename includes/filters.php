<?php

/**
* UTF-8 Defucker
*
* Takes stuff like this:
*   SＴ0ｐ ＤＤ0ｓＩng Aｔ! SＴoP Dｄ0Ｓ1ｎG ａT! SｔｏP DD0ｓｉＮ9 @ｔ! St0Ｐ dＤOsIｎ9 ＡT! sＴＯp DＤ0ｓINg ａ+! ｓTｏp DＤ0ｓ|Ｎ9 AＴ! S+ｏp DｄｏSｉnG Ａｔ! S+Oｐ ＤDＯｓIｎｇ AT! ｓ+ｏＰ DｄosｉＮＧ at!
*
*   ｂtW, ＨＥＲ3'S ｔHＥ TＲUe CＯloRs OF ＹOｕr ＧＬＯｒiＯUs ｈ3Ｒo <HrｉsｔoPｈＥr ＰＯｏL3: HＴTP://Wwｗ.@ｎｏＮt@LK.{0M/DUＭｐ/ＭｏOtAｒD.Ｔｘt
*
* Converts it into this (actual output given the above):
*   STOP DDOSING AT! STOP DDOSING AT! STOP DDOSING AT! STOP DDOSING AT! STOP DDOSING AT! STOP DDOSING AT! STOP DDOSING AT! STOP DDOSING AT! STOP DDOSING AT!
*
*   BTW, HERE'S THE TRUE COLORS OF YOUR GLORIOUS HERO CHRISTOPHER POOLE: HTTP://WWW.ANONTALK.COM/DUMP/MOOTARD.TXT
*
* Public domain, I don't give a fuck about what you do with this.
*/

define('PUNISH_REPLACE',0);
define('PUNISH_ERROR',	1);
define('PUNISH_BAN',	2);

// Adjust as needed.
define('ANTIRANDOM_MAX_SCORE',20);

// Turn right-side chars into left-side.
$CONVERSIONS=array (
  'O' => array('0'),
  'E' => array('3','EUR'),
  'C' => array('<','{','['),
  'I' => array('1','|'), // This may cause problems but I don't care
  'G' => array('9'),	 // Same here
  'A' => array('@'),
  'T' => array('+'),
  'S' => array('5')
);

$ANTIRANDOM=array(
	"aj" => "fqtvxz",
	"aq" => "deghjkmnprtxyz",
	"av" => "bfhjqwxz",
	"az" => "jwx",
	"bd" => "bghjkmpqvxz",
	"bf" => "bcfgjknpqvwxyz",
	"bg" => "bdfghjkmnqstvxz",
	"bh" => "bfhjkmnqvwxz",
	"bj" => "bcdfghjklmpqtvwxyz",
	"bk" => "dfjkmqrvwxyz",
	"bl" => "bgpqwxz",
	"bm" => "bcdflmnqz",
	"bn" => "bghjlmnpqtvwx",
	"bp" => "bfgjknqvxz",
	"bq" => "bcdefghijklmnopqrstvwxyz",
	"bt" => "dgjkpqtxz",
	"bv" => "bfghjklnpqsuvwxz",
	"bw" => "bdfjknpqsuwxyz",
	"bx" => "abcdfghijklmnopqtuvwxyz",
	"bz" => "bcdfgjklmnpqrstvwxz",
	"cb" => "bfghjkpqyz",
	"cc" => "gjqxz",
	"cd" => "hjkqvwxz",
	"cf" => "gjknqvwyz",
	"cg" => "bdfgjkpqvz",
	"cl" => "fghjmpqxz",
	"cm" => "bjkqv",
	"cn" => "bghjkpqwxz",
	"cp" => "gjkvxyz",
	"cq" => "abcdefghijklmnopqsvwxyz",
	"cr" => "gjqx",
	"cs" => "gjxz",
	"cv" => "bdfghjklmnquvwxyz",
	"cx" => "abdefghjklmnpqrstuvwxyz",
	"cy" => "jqy",
	"cz" => "bcdfghjlpqrtvwxz",
	"db" => "bdgjnpqtxz",
	"dc" => "gjqxz",
	"dd" => "gqz",
	"df" => "bghjknpqvxyz",
	"dg" => "bfgjqvxz",
	"dh" => "bfkmnqwxz",
	"dj" => "bdfghjklnpqrwxz",
	"dk" => "cdhjkpqrtuvwxz",
	"dl" => "bfhjknqwxz",
	"dm" => "bfjnqw",
	"dn" => "fgjkmnpqvwz",
	"dp" => "bgjkqvxz",
	"dq" => "abcefghijkmnopqtvwxyz",
	"dr" => "bfkqtvx",
	"dt" => "qtxz",
	"dv" => "bfghjknqruvwyz",
	"dw" => "cdfjkmnpqsvwxz",
	"dx" => "abcdeghjklmnopqrsuvwxyz",
	"dy" => "jyz",
	"dz" => "bcdfgjlnpqrstvxz",
	"eb" => "jqx",
	"eg" => "cjvxz",
	"eh" => "hxz",
	"ej" => "fghjpqtwxyz",
	"ek" => "jqxz",
	"ep" => "jvx",
	"eq" => "bcghijkmotvxyz",
	"ev" => "bfpq",
	"fc" => "bdjkmnqvxz",
	"fd" => "bgjklqsvyz",
	"fg" => "fgjkmpqtvwxyz",
	"fh" => "bcfghjkpqvwxz",
	"fj" => "bcdfghijklmnpqrstvwxyz",
	"fk" => "bcdfghjkmpqrstvwxz",
	"fl" => "fjkpqxz",
	"fm" => "dfhjlmvwxyz",
	"fn" => "bdfghjklnqrstvwxz",
	"fp" => "bfjknqtvwxz",
	"fq" => "abcefghijklmnopqrstvwxyz",
	"fr" => "nqxz",
	"fs" => "gjxz",
	"ft" => "jqx",
	"fv" => "bcdfhjklmnpqtuvwxyz",
	"fw" => "bcfgjklmpqstuvwxyz",
	"fx" => "bcdfghjklmnpqrstvwxyz",
	"fy" => "ghjpquvxy",
	"fz" => "abcdfghjklmnpqrtuvwxyz",
	"gb" => "bcdknpqvwx",
	"gc" => "gjknpqwxz",
	"gd" => "cdfghjklmqtvwxz",
	"gf" => "bfghjkmnpqsvwxyz",
	"gg" => "jkqvxz",
	"gj" => "bcdfghjklmnpqrstvwxyz",
	"gk" => "bcdfgjkmpqtvwxyz",
	"gl" => "fgjklnpqwxz",
	"gm" => "dfjkmnqvxz",
	"gn" => "jkqvxz",
	"gp" => "bjknpqtwxyz",
	"gq" => "abcdefghjklmnopqrsvwxyz",
	"gr" => "jkqt",
	"gt" => "fjknqvx",
	"gu" => "qwx",
	"gv" => "bcdfghjklmpqstvwxyz",
	"gw" => "bcdfgjknpqtvwxz",
	"gx" => "abcdefghjklmnopqrstvwxyz",
	"gy" => "jkqxy",
	"gz" => "bcdfgjklmnopqrstvxyz",
	"hb" => "bcdfghjkqstvwxz",
	"hc" => "cjknqvwxz",
	"hd" => "fgjnpvz",
	"hf" => "bfghjkmnpqtvwxyz",
	"hg" => "bcdfgjknpqsxyz",
	"hh" => "bcgklmpqrtvwxz",
	"hj" => "bcdfgjkmpqtvwxyz",
	"hk" => "bcdgkmpqrstvwxz",
	"hl" => "jxz",
	"hm" => "dhjqrvwxz",
	"hn" => "jrxz",
	"hp" => "bjkmqvwxyz",
	"hq" => "abcdefghijklmnopqrstvwyz",
	"hr" => "cjqx",
	"hs" => "jqxz",
	"hv" => "bcdfgjklmnpqstuvwxz",
	"hw" => "bcfgjklnpqsvwxz",
	"hx" => "abcdefghijklmnopqrstuvwxyz",
	"hz" => "bcdfghjklmnpqrstuvwxz",
	"ib" => "jqx",
	"if" => "jqvwz",
	"ih" => "bgjqx",
	"ii" => "bjqxy",
	"ij" => "cfgqxy",
	"ik" => "bcfqx",
	"iq" => "cdefgjkmnopqtvxyz",
	"iu" => "hiwxy",
	"iv" => "cfgmqx",
	"iw" => "dgjkmnpqtvxz",
	"ix" => "jkqrxz",
	"iy" => "bcdfghjklpqtvwx",
	"jb" => "bcdghjklmnopqrtuvwxyz",
	"jc" => "cfgjkmnopqvwxy",
	"jd" => "cdfghjlmnpqrtvwx",
	"jf" => "abcdfghjlnopqrtuvwxyz",
	"jg" => "bcdfghijklmnopqstuvwxyz",
	"jh" => "bcdfghjklmnpqrxyz",
	"jj" => "bcdfghjklmnopqrstuvwxyz",
	"jk" => "bcdfghjknqrtwxyz",
	"jl" => "bcfghjmnpqrstuvwxyz",
	"jm" => "bcdfghiklmnqrtuvwyz",
	"jn" => "bcfjlmnpqsuvwxz",
	"jp" => "bcdfhijkmpqstvwxyz",
	"jq" => "abcdefghijklmnopqrstuvwxyz",
	"jr" => "bdfhjklpqrstuvwxyz",
	"js" => "bfgjmoqvxyz",
	"jt" => "bcdfghjlnpqrtvwxz",
	"jv" => "abcdfghijklpqrstvwxyz",
	"jw" => "bcdefghijklmpqrstuwxyz",
	"jx" => "abcdefghijklmnopqrstuvwxyz",
	"jy" => "bcdefghjkpqtuvwxyz",
	"jz" => "bcdfghijklmnopqrstuvwxyz",
	"kb" => "bcdfghjkmqvwxz",
	"kc" => "cdfgjknpqtwxz",
	"kd" => "bfghjklmnpqsvwxyz",
	"kf" => "bdfghjkmnpqsvwxyz",
	"kg" => "cghjkmnqtvwxyz",
	"kh" => "cfghjkqx",
	"kj" => "bcdfghjkmnpqrstwxyz",
	"kk" => "bcdfgjmpqswxz",
	"kl" => "cfghlmqstwxz",
	"km" => "bdfghjknqrstwxyz",
	"kn" => "bcdfhjklmnqsvwxz",
	"kp" => "bdfgjkmpqvxyz",
	"kq" => "abdefghijklmnopqrstvwxyz",
	"kr" => "bcdfghjmqrvwx",
	"ks" => "jqx",
	"kt" => "cdfjklqvx",
	"ku" => "qux",
	"kv" => "bcfghjklnpqrstvxyz",
	"kw" => "bcdfgjklmnpqsvwxz",
	"kx" => "abcdefghjklmnopqrstuvwxyz",
	"ky" => "vxy",
	"kz" => "bcdefghjklmnpqrstuvwxyz",
	"lb" => "cdgkqtvxz",
	"lc" => "bqx",
	"lg" => "cdfgpqvxz",
	"lh" => "cfghkmnpqrtvx",
	"lk" => "qxz",
	"ln" => "cfjqxz",
	"lp" => "jkqxz",
	"lq" => "bcdefhijklmopqrstvwxyz",
	"lr" => "dfgjklmpqrtvwx",
	"lv" => "bcfhjklmpwxz",
	"lw" => "bcdfgjknqxz",
	"lx" => "bcdfghjklmnpqrtuwz",
	"lz" => "cdjptvxz",
	"mb" => "qxz",
	"md" => "hjkpvz",
	"mf" => "fkpqvwxz",
	"mg" => "cfgjnpqsvwxz",
	"mh" => "bchjkmnqvx",
	"mj" => "bcdfghjknpqrstvwxyz",
	"mk" => "bcfgklmnpqrvwxz",
	"ml" => "jkqz",
	"mm" => "qvz",
	"mn" => "fhjkqxz",
	"mq" => "bdefhjklmnopqtwxyz",
	"mr" => "jklqvwz",
	"mt" => "jkq",
	"mv" => "bcfghjklmnqtvwxz",
	"mw" => "bcdfgjklnpqsuvwxyz",
	"mx" => "abcefghijklmnopqrstvwxyz",
	"mz" => "bcdfghjkmnpqrstvwxz",
	"nb" => "hkmnqxz",
	"nf" => "bghqvxz",
	"nh" => "fhjkmqtvxz",
	"nk" => "qxz",
	"nl" => "bghjknqvwxz",
	"nm" => "dfghjkqtvwxz",
	"np" => "bdjmqwxz",
	"nq" => "abcdfghjklmnopqrtvwxyz",
	"nr" => "bfjkqstvx",
	"nv" => "bcdfgjkmnqswxz",
	"nw" => "dgjpqvxz",
	"nx" => "abfghjknopuyz",
	"nz" => "cfqrxz",
	"oc" => "fjvw",
	"og" => "qxz",
	"oh" => "fqxz",
	"oj" => "bfhjmqrswxyz",
	"ok" => "qxz",
	"oq" => "bcdefghijklmnopqrstvwxyz",
	"ov" => "bfhjqwx",
	"oy" => "qxy",
	"oz" => "fjpqtvx",
	"pb" => "fghjknpqvwz",
	"pc" => "gjq",
	"pd" => "bgjkvwxz",
	"pf" => "hjkmqtvwyz",
	"pg" => "bdfghjkmqsvwxyz",
	"ph" => "kqvx",
	"pk" => "bcdfhjklmpqrvx",
	"pl" => "ghkqvwx",
	"pm" => "bfhjlmnqvwyz",
	"pn" => "fjklmnqrtvwz",
	"pp" => "gqwxz",
	"pq" => "abcdefghijklmnopqstvwxyz",
	"pr" => "hjkqrwx",
	"pt" => "jqxz",
	"pv" => "bdfghjklquvwxyz",
	"pw" => "fjkmnpqsuvwxz",
	"px" => "abcdefghijklmnopqrstuvwxyz",
	"pz" => "bdefghjklmnpqrstuvwxyz",
	"qa" => "ceghkopqxy",
	"qb" => "bcdfghjklmnqrstuvwxyz",
	"qc" => "abcdfghijklmnopqrstuvwxyz",
	"qd" => "defghijklmpqrstuvwxyz",
	"qe" => "abceghjkmopquwxyz",
	"qf" => "abdfghijklmnopqrstuvwxyz",
	"qg" => "abcdefghijklmnopqrtuvwxz",
	"qh" => "abcdefghijklmnopqrstuvwxyz",
	"qi" => "efgijkmpwx",
	"qj" => "abcdefghijklmnopqrstuvwxyz",
	"qk" => "abcdfghijklmnopqrsuvwxyz",
	"ql" => "abcefghjklmnopqrtuvwxyz",
	"qm" => "bdehijklmnoqrtuvxyz",
	"qn" => "bcdefghijklmnoqrtuvwxyz",
	"qo" => "abcdefgijkloqstuvwxyz",
	"qp" => "abcdefghijkmnopqrsuvwxyz",
	"qq" => "bcdefghijklmnopstwxyz",
	"qr" => "bdefghijklmnoqruvwxyz",
	"qs" => "bcdefgijknqruvwxz",
	"qt" => "befghjklmnpqtuvwxz",
	"qu" => "cfgjkpwz",
	"qv" => "abdefghjklmnopqrtuvwxyz",
	"qw" => "bcdfghijkmnopqrstuvwxyz",
	"qx" => "abcdefghijklmnopqrstuvwxyz",
	"qy" => "abcdefghjklmnopqrstuvwxyz",
	"qz" => "abcdefghijklmnopqrstuvwxyz",
	"rb" => "fxz",
	"rg" => "jvxz",
	"rh" => "hjkqrxz",
	"rj" => "bdfghjklmpqrstvwxz",
	"rk" => "qxz",
	"rl" => "jnq",
	"rp" => "jxz",
	"rq" => "bcdefghijklmnopqrtvwxy",
	"rr" => "jpqxz",
	"rv" => "bcdfghjmpqrvwxz",
	"rw" => "bfgjklqsvxz",
	"rx" => "bcdfgjkmnopqrtuvwxz",
	"rz" => "djpqvxz",
	"sb" => "kpqtvxz",
	"sd" => "jqxz",
	"sf" => "bghjkpqw",
	"sg" => "cgjkqvwxz",
	"sj" => "bfghjkmnpqrstvwxz",
	"sk" => "qxz",
	"sl" => "gjkqwxz",
	"sm" => "fkqwxz",
	"sn" => "dhjknqvwxz",
	"sq" => "bfghjkmopstvwxz",
	"sr" => "jklqrwxz",
	"sv" => "bfhjklmnqtwxyz",
	"sw" => "jkpqvwxz",
	"sx" => "bcdefghjklmnopqrtuvwxyz",
	"sy" => "qxy",
	"sz" => "bdfgjpqsvxz",
	"tb" => "cghjkmnpqtvwx",
	"tc" => "jnqvx",
	"td" => "bfgjkpqtvxz",
	"tf" => "ghjkqvwyz",
	"tg" => "bdfghjkmpqsx",
	"tj" => "bdfhjklmnpqstvwxyz",
	"tk" => "bcdfghjklmpqvwxz",
	"tl" => "jkqwxz",
	"tm" => "bknqtwxz",
	"tn" => "fhjkmqvwxz",
	"tp" => "bjpqvwxz",
	"tq" => "abdefhijklmnopqrstvwxyz",
	"tr" => "gjqvx",
	"tv" => "bcfghjknpquvwxz",
	"tw" => "bcdfjknqvz",
	"tx" => "bcdefghjklmnopqrsuvwxz",
	"tz" => "jqxz",
	"uc" => "fjmvx",
	"uf" => "jpqvx",
	"ug" => "qvx",
	"uh" => "bcgjkpvxz",
	"uj" => "wbfghklmqvwx",
	"uk" => "fgqxz",
	"uq" => "bcdfghijklmnopqrtwxyz",
	"uu" => "fijkqvwyz",
	"uv" => "bcdfghjkmpqtwxz",
	"uw" => "dgjnquvxyz",
	"ux" => "jqxz",
	"uy" => "jqxyz",
	"uz" => "fgkpqrx",
	"vb" => "bcdfhijklmpqrtuvxyz",
	"vc" => "bgjklnpqtvwxyz",
	"vd" => "bdghjklnqvwxyz",
	"vf" => "bfghijklmnpqtuvxz",
	"vg" => "bcdgjkmnpqtuvwxyz",
	"vh" => "bcghijklmnpqrtuvwxyz",
	"vj" => "abcdfghijklmnpqrstuvwxyz",
	"vk" => "bcdefgjklmnpqruvwxyz",
	"vl" => "hjkmpqrvwxz",
	"vm" => "bfghjknpquvxyz",
	"vn" => "bdhjkmnpqrtuvwxz",
	"vp" => "bcdeghjkmopqtuvwyz",
	"vq" => "abcdefghijklmnopqrstvwxyz",
	"vr" => "fghjknqrtvwxz",
	"vs" => "dfgjmqz",
	"vt" => "bdfgjklmnqtx",
	"vu" => "afhjquwxy",
	"vv" => "cdfghjkmnpqrtuwxz",
	"vw" => "abcdefghijklmnopqrtuvwxyz",
	"vx" => "abcefghjklmnopqrstuvxyz",
	"vy" => "oqx",
	"vz" => "abcdefgjklmpqrstvwxyz",
	"wb" => "bdfghjpqtvxz",
	"wc" => "bdfgjkmnqvwx",
	"wd" => "dfjpqvxz",
	"wf" => "cdghjkmqvwxyz",
	"wg" => "bcdfgjknpqtvwxyz",
	"wh" => "cdghjklpqvwxz",
	"wj" => "bfghijklmnpqrstvwxyz",
	"wk" => "cdfgjkpqtuvxz",
	"wl" => "jqvxz",
	"wm" => "dghjlnqtvwxz",
	"wp" => "dfgjkpqtvwxz",
	"wq" => "abcdefghijklmnopqrstvwxyz",
	"wr" => "cfghjlmpqwx",
	"wt" => "bdgjlmnpqtvx",
	"wu" => "aikoquvwy",
	"wv" => "bcdfghjklmnpqrtuvwxyz",
	"ww" => "bcdgkpqstuvxyz",
	"wx" => "abcdefghijklmnopqrstuvwxz",
	"wy" => "jquwxy",
	"wz" => "bcdfghjkmnopqrstuvwxz",
	"xa" => "ajoqy",
	"xb" => "bcdfghjkmnpqsvwxz",
	"xc" => "bcdgjkmnqsvwxz",
	"xd" => "bcdfghjklnpqstuvwxyz",
	"xf" => "bcdfghjkmnpqtvwxyz",
	"xg" => "bcdfghjkmnpqstvwxyz",
	"xh" => "cdfghjkmnpqrstvwxz",
	"xi" => "jkqy",
	"xj" => "abcdefghijklmnopqrstvwxyz",
	"xk" => "abcdfghjkmnopqrstuvwxyz",
	"xl" => "bcdfghjklmnpqrvwxz",
	"xm" => "bcdfghjknpqvwxz",
	"xn" => "bcdfghjklmnpqrvwxyz",
	"xp" => "bcfjknpqvxz",
	"xq" => "abcdefghijklmnopqrstvwxyz",
	"xr" => "bcdfghjklnpqrsvwyz",
	"xs" => "bdfgjmnqrsvxz",
	"xt" => "jkpqvwxz",
	"xu" => "fhjkquwx",
	"xv" => "bcdefghjklmnpqrsuvwxyz",
	"xw" => "bcdfghjklmnpqrtuvwxyz",
	"xx" => "bcdefghjkmnpqrstuwyz",
	"xy" => "jxy",
	"xz" => "abcdefghjklmnpqrstuvwxyz",
	"yb" => "cfghjmpqtvwxz",
	"yc" => "bdfgjmpqsvwx",
	"yd" => "chjkpqvwx",
	"yf" => "bcdghjmnpqsvwx",
	"yg" => "cfjkpqtxz",
	"yh" => "bcdfghjkpqx",
	"yi" => "hjqwxy",
	"yj" => "bcdfghjklmnpqrstvwxyz",
	"yk" => "bcdfgpqvwxz",
	"ym" => "dfgjqvxz",
	"yp" => "bcdfgjkmqxz",
	"yq" => "abcdefghijklmnopqrstvwxyz",
	"yr" => "jqx",
	"yt" => "bcfgjnpqx",
	"yv" => "bcdfghjlmnpqstvwxz",
	"yw" => "bfgjklmnpqstuvwxz",
	"yx" => "bcdfghjknpqrstuvwxz",
	"yy" => "bcdfghjklpqrstvwxz",
	"yz" => "bcdfjklmnpqtvwx",
	"zb" => "dfgjklmnpqstvwxz",
	"zc" => "bcdfgjmnpqstvwxy",
	"zd" => "bcdfghjklmnpqstvwxy",
	"zf" => "bcdfghijkmnopqrstvwxyz",
	"zg" => "bcdfgjkmnpqtvwxyz",
	"zh" => "bcfghjlpqstvwxz",
	"zj" => "abcdfghjklmnpqrstuvwxyz",
	"zk" => "bcdfghjklmpqstvwxz",
	"zl" => "bcdfghjlnpqrstvwxz",
	"zm" => "bdfghjklmpqstvwxyz",
	"zn" => "bcdfghjlmnpqrstuvwxz",
	"zp" => "bcdfhjklmnpqstvwxz",
	"zq" => "abcdefghijklmnopqrstvwxyz",
	"zr" => "bcfghjklmnpqrstvwxyz",
	"zs" => "bdfgjlmnqrsuwxyz",
	"zt" => "bcdfgjkmnpqtuvwxz",
	"zu" => "ajqx",
	"zv" => "bcdfghjklmnpqrstuvwxyz",
	"zw" => "bcdfghjklmnpqrstuvwxyz",
	"zx" => "abcdefghijklmnopqrstuvwxyz",
	"zy" => "fxy",
	"zz" => "cdfhjnpqrvx"
);
/*
	'A' => array('4',	"/\\",	'@',	"/-\\",	'^',	'aye',	'∂',	'ci',	'λ',	'Z'),
	'B' => array('6',	'8',	'13',	'I3',	'|3',	'ß',	'P>',	'|:',	'!3',	'(3',	'/3',	')3',	']3'),
	'C' => array('¢',	'<',	'(',	'{',	'(c)',	'sea'),
	'D' => array('|)',	'|o',	'∂',	'])',	'[)',	'I>',	'|>',	'?',	'T)',	'0',	'ð',	'cl'),
	'E' => array('3',	'&',	'€',	'£',	'ë',	'[-',	'|=-',	'ə'),
	'F' => array(']='	'ph',	'}',	'|=',	'(=',	'I='),
	'G' => array('6',	'&',	'(_+',	'9',	'C-',	'gee',	'(γ,',	'(_-',	'cj'),
	'H' => array('/-/',	'[-]',	']-[',	')-(',	'(-)',	':-:',	'|~|',	'|-|',	']~[',	'}{',	'?',	'}-{',	'#',	'aych'),
	'I' => array('1',	'!',	'|',	'eye',	'3y3',	'ai',	'¡',	'][',	':',	']'),
	'J' => array('_|',	'_/',	']',	'¿',	'</',	'(/',	'_7',	'_)',	'ʝ'),
	'K' => array('|X',	'|<',	'|{',	'ɮ'),
	'L' => array('1',	'£',	'1_',	'|',	'|_',	'lJ ',	'¬'),
	'M' => array('|v|',	'em',	']V[',	'(T)',	'[V]',	'nn',	'//\\//\\',	'|\/|',	"/\\/\\",	'(u)',	'(V)',	'(\/)',	"/|\\",	'^^',	'/|/|',	'//.',	".\\",	"/^^\\",	"/V\\",	"[]\\/[]",	'|^^|'),
	'N' => array('|\|',	'^/',	'//\\//',	'/\/',	'[\]',	'<\>',	'{\}',	'[]\',	'// []',	'/V',	'₪',	'[]\[]',	']\[',	'~'),
	'O' => array('0',	'()',	'oh',	'[]',	'p',	'¤',	'Ω'),
	'P' => array('|*',	'|o',	'|º',	'|^(o)',	'|>',	'|"',	'?',	'9',	'[]D',	'|̊',	'|7',	'q',	'þ',	'¶',	'℗',	'|D'),
	'Q' => array('(_,)',	'()_',	'0_',	'<|',	'cue',	'9',	'O,',	'(,)',	'(),',	'¶'),
	'R' => array('2',	'12',	'|?',	'/2',	'I2',	'|^',	'|~',	'lz',	'(r)',	'|2',	'[z',	'|`',	'l2',	'Я'),',	'|2',	'.-',	'ʁ'),
	'S' => array('5',	'$',	'z',	'§',	'ehs',	'es',	'ez'),
	'T' => array('7',	'+',	'<nowiki>-|-</nowiki>',	'1',	'']['',	'†'),
	'U' => array('M',	'|_|',	'Y3W',	'L|',	'µ',	'[_]',	'\_/',	'\_\',	'/_/',	'(_)'),
	'V' => array('\/',	'√',	'\\//</code>
	'W' => array('\/\/',	'vv',	''//',	'\\'',	'\^/',	'(n)',	'\V/',	'\X/',	'\|/',	'\_|_/',	'\\//\\//',	'\_:_/',	'(/\)',	']I[',	'LL1',	'UU',	'Ш',	'ɰ'),
	'X' => array('%',	'><',	'Ж',	'}{',	'ecks',	'×',	'* <br />)(',	'ex <br /[/code>
	'Y' => array('j',	'`/',	'`(',	'-/',	''/',	'Ψ',	'φ',	'λ',	'Ч ',	'¥'),
	'Z' => array('2',	'~/_',	'%',	'>_',	'ʒ',	'7_</code>
*/

$REPLACEMENTS=array();
// Only use this for filters or you'll permafuck the comment.
// 
// Usage:  checkForBannedPhrases(defuck_comment($input));
function defuck_comment($sus)
{
	global $CONVERSIONS,$REPLACEMENTS;

	// Convert UTF-8 to ISO-8859-1 with transliteration (turns the weird characters into close approximations)
	// <HrｉsｔoPｈＥr ＰＯｏL3 -> <HRISTOPHER POOL3
	$sus=strtoupper(iconv("UTF-8", "ISO-8859-1//TRANSLIT",$sus));

	// Change some common dodge attempts (like < or { instead of C) to their counterparts.
	foreach ($CONVERSIONS as $o=>$ca)
	{
		foreach($ca as $c)
			$sus=str_replace($c,$o,$sus);
	}
	// <HrｉsｔoPｈＥr ＰＯｏL3 -> CHRISTOPHER POOLE
	return $sus;
}

function GatherReplacements($sus)
{
	$r=array();
	$unf=defuck_comment($sus);
	for($i=0;$i<strlen($unf);$i++)
	{
		$o=$sus[$i];
		$c=$unf[$i];
		if(!array_key_exists($c,$r))
			$r[$c]=array();
		$r[$c][]=$o;
	}
	return $r;
}

function AddFilterToQueue($text)
{
	$text=defuck_comment($text);
	if(!in_array($text,$_SESSION['2BFiltered']))
		$_SESSION['2BFiltered'][]=$text;
}

function AddWordFilter($text,$reason,$punishtype,$punishduration,$replacement)
{
	global $User;
	$text		= DB::Q($text);
	$reason		= DB::Q($reason);
	$replacement	= DB::Q($replacement);

	$res=DB::Execute("SELECT 1 FROM {P}Filters WHERE filter=$text");
	if($res->RecordCount()>0) return;
	
	$f=array('filText'=>$text,'filReason'=>$reason,'filPunishType'=>intval($punishtype),'filPunishDuration'=>$punishduration,'filReplacement'=>$replacement);
	DB::EasyInsert('{P}Filters',$f);
}

function DelWordFilter($text)
{
	DB::Execute('DELETE FROM {P}Filters WHERE filID='.intval($id));
}

function Check4Filtered($headline,$body,$returnbool=false)
{
	global $ANTIRANDOM,$User;
	$hl_df=defuck_comment($headline);
	$b_df=defuck_comment($body);
	
	$res=DB::Execute("SELECT filText,filReason,filPunishType,filPunishDuration,filReplacement FROM {P}Filters");	
	$dbg='';
	while(list($fText,$fReason,$fPunishment,$fPunishTime,$fReplacement)=$res->FetchRow())
	{
		// Fastest string search method.
		$idx=strpos($hl_df.' '.$b_df,$fText);
		if($idx===false)
			continue;
		if($returnbool===true) return true;
		switch($fPunishment)
		{
			case 0: // Just replace
				$headline=str_ireplace($fText,$fReplacement,$headline);
				$body=str_ireplace($fText,$fReplacement,$body);
				
				break;
			case 1: // 403
				header('HTTP/1.1 403 Forbidden');
				Output::HardError("<b>ATBBS has denied your post, as it contains &quot;".htmlentities($fText)."&quot;, which is banned for the following reason:</b><br />$fReason");
				break;
			case 2: // Ban
				AddBan($User->ID,$_SERVER['REMOTE_ADDR'],$fPunishTime,'<span class="tag filter">Filter</span>'.$fReason,0);
				break;
			default:
				// Ignore.
				break;
		}
	}
	$score=GetRandomScore($headline.' '.$body);
	if($score>=ANTIRANDOM_MAX_SCORE)
	{
		if($returnbool===true) return true;
		header('HTTP/1.1 403 Forbidden');
		Output::HardError("Your post contains random data (Score: $score, Max score: ".ANTIRANDOM_MAX_SCORE."). Knock it the fuck off.");
		exit;
	}
	Check4Ban(true);
	if($returnbool===true)return false;
	return array($headline,$body);
}

function GetRandomScore($input)
{
	//echo $input;
	global $ANTIRANDOM;
	$score=0;
	foreach($ANTIRANDOM as $first=>$followchars)
	{
		for($i=0;$i<strlen($followchars);$i++)
		{
			$fc=substr($followchars,0,1);
			if(stripos($input,$first.$fc)!==false)
			{
				$score++;
			}
		}
	}
	return $score;
}

function OutputWithLineNumbers($in)
{
	$o="<p>\n\t<ol class=\"codelines\">\n\t\t";
	$dark=true;
	if(!is_array($in))
		$a=explode("\n",$in);
	else
		$a=explode("\n",$in[1]);
	foreach($a as $l)
	{
		$l=str_replace("\r","",$l);
		$l=str_replace("\n","",$l);
		$l=trim($l);
		if($l!='')
		{
			$dark=!$dark;
			$o.= '<li class="'.(($dark)?'dark':'light').'">'.htmlspecialchars($l)."</li>\n\t\t";
		}
	}
	$o.= '</ol></p>';
	return $o;
}
