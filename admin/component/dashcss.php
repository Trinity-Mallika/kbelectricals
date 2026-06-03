 <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
 <style>
     :root {
         --blue: #1a6ca8;
         --blue2: #2980b9;
         --light: #eaf2fb;
         --bg: #f4f6f9;
         --border: #dde3ea;
         --text: #1a3f5c;
         --muted: #8a9ab0;
     }

     body {
         background: var(--bg);
     }

     .dash-wrap {
         padding: 16px 20px 40px;
         min-height: 100vh;
     }

     /* ── Page header ── */
     .dash-header {
         display: flex;
         justify-content: space-between;
         align-items: center;
         margin-bottom: 16px;
     }

     .dash-title {
         font-size: .95rem;
         font-weight: 700;
         color: var(--text);
         display: flex;
         align-items: center;
         gap: 8px;
     }

     .dash-title::before {
         content: '';
         display: inline-block;
         width: 4px;
         height: 18px;
         background: var(--blue);
         border-radius: 2px;
     }

     .dash-date {
         font-size: .72rem;
         color: var(--muted);
     }

     /* ── Stat row ── */
     .stat-grid {
         display: grid;
         grid-template-columns: repeat(5, 1fr);
         gap: 12px;
         margin-bottom: 16px;
     }

     @media(max-width:1100px) {
         .stat-grid {
             grid-template-columns: repeat(3, 1fr);
         }
     }

     @media(max-width:680px) {
         .stat-grid {
             grid-template-columns: repeat(2, 1fr);
         }
     }

     .stat-card {
         background: #fff;
         border-radius: 10px;
         border: 1px solid var(--border);
         padding: 14px 14px 12px;
         box-shadow: 0 1px 4px rgba(0, 0, 0, .06);
         text-decoration: none;
         display: block;
         position: relative;
         overflow: hidden;
         transition: transform .18s, box-shadow .18s;
     }

     .stat-card:hover {
         transform: translateY(-3px);
         box-shadow: 0 6px 18px rgba(0, 0, 0, .1);
     }

     .stat-card::before {
         content: '';
         position: absolute;
         top: 0;
         left: 0;
         right: 0;
         height: 3px;
         background: var(--c, #1a6ca8);
     }

     .stat-icon {
         font-size: 2rem;
         opacity: .3;
         position: absolute;
         right: 10px;
         bottom: 6px;
     }

     .stat-label {
         font-size: .68rem;
         font-weight: 700;
         color: var(--muted);
         text-transform: uppercase;
         letter-spacing: .4px;
         margin-bottom: 5px;
     }

     .stat-value {
         font-size: 1.5rem;
         font-weight: 700;
         color: var(--text);
         line-height: 1;
     }

     .stat-sub {
         font-size: .68rem;
         color: var(--muted);
         margin-top: 4px;
     }

     .pill {
         display: inline-block;
         padding: 1px 7px;
         border-radius: 10px;
         font-size: .65rem;
         font-weight: 600;
     }

     .pill-warn {
         background: #fff3cd;
         color: #856404;
     }

     .pill-ok {
         background: #d1fae5;
         color: #065f46;
     }

     .pill-blue {
         background: var(--light);
         color: var(--blue);
     }

     /* ── Section label ── */
     .sec-label {
         font-size: .72rem;
         font-weight: 700;
         color: var(--muted);
         text-transform: uppercase;
         letter-spacing: .5px;
         margin: 18px 0 8px;
         display: flex;
         align-items: center;
         gap: 6px;
     }

     .sec-label::after {
         content: '';
         flex: 1;
         height: 1px;
         background: var(--border);
     }

     /* ── Panel ── */
     .panel {
         background: #fff;
         border-radius: 10px;
         border: 1px solid var(--border);
         box-shadow: 0 1px 4px rgba(0, 0, 0, .06);
         overflow: hidden;
     }

     .panel-head {
         background: var(--blue);
         padding: 10px 14px;
         display: flex;
         justify-content: space-between;
         align-items: center;
     }

     .panel-head .ph-title {
         color: #fff;
         font-size: .82rem;
         font-weight: 600;
     }

     .panel-head a {
         color: rgba(255, 255, 255, .75);
         font-size: .7rem;
         text-decoration: none;
     }

     .panel-head a:hover {
         color: #fff;
     }

     /* ── Grids ── */
     .g2 {
         display: grid;
         grid-template-columns: 1.2fr 2fr;
         gap: 14px;
     }

     .g3 {
         display: grid;
         grid-template-columns: 1fr 1fr 1fr;
         gap: 14px;
     }

     .g22 {
         display: grid;
         grid-template-columns: 1fr 1fr;
         gap: 14px;
     }

     @media(max-width:1024px) {

         .g2,
         .g3,
         .g22 {
             grid-template-columns: 1fr;
         }
     }

     /* ── Mini table ── */
     .mini-table {
         width: 100%;
         border-collapse: collapse;
         font-size: .79rem;
     }

     .mini-table th {
         background: var(--light);
         color: var(--blue);
         font-size: .65rem;
         font-weight: 700;
         text-transform: uppercase;
         letter-spacing: .4px;
         padding: 7px 11px;
         border-bottom: 1px solid var(--border);
         text-align: left;
     }

     .mini-table td {
         padding: 7px 11px;
         border-bottom: 1px solid #f0f4f8;
         color: #34495e;
         vertical-align: middle;
     }

     .mini-table tr:last-child td {
         border-bottom: none;
     }

     .mini-table tr:hover td {
         background: #f8fafc;
     }

     /* ── Brand target bars ── */
     .brand-grid {
         display: grid;
         grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
         gap: 12px;
         padding: 14px;
     }

     .brand-card {
         background: var(--bg);
         border: 1px solid var(--border);
         border-radius: 8px;
         padding: 12px;
         position: relative;
     }

     .brand-name {
         font-size: .75rem;
         font-weight: 700;
         color: var(--text);
         margin-bottom: 6px;
         white-space: nowrap;
         overflow: hidden;
         text-overflow: ellipsis;
     }

     .brand-nums {
         display: flex;
         justify-content: space-between;
         font-size: .68rem;
         color: var(--muted);
         margin-bottom: 5px;
     }

     .brand-nums strong {
         color: var(--text);
     }

     .bpbar-wrap {
         background: #dde3ea;
         border-radius: 20px;
         height: 7px;
         overflow: hidden;
     }

     .bpbar-fill {
         height: 100%;
         border-radius: 20px;
         background: linear-gradient(90deg, var(--blue), var(--blue2));
         transition: width 1s ease;
     }

     .bpbar-fill.over {
         background: linear-gradient(90deg, #27ae60, #2ecc71);
     }

     .brand-pct {
         font-size: .78rem;
         font-weight: 700;
         color: var(--blue);
         text-align: right;
         margin-top: 4px;
     }

     /* ── Rep performance ── */
     .rep-row {
         display: grid;
         grid-template-columns: 1fr 70px 70px 70px 50px;
         gap: 6px;
         align-items: center;
         padding: 8px 14px;
         border-bottom: 1px solid #f0f4f8;
         font-size: .79rem;
     }

     .rep-row:last-child {
         border-bottom: none;
     }

     .rep-row:hover {
         background: #f8fafc;
     }

     .rep-head {
         background: var(--light);
         font-weight: 700;
         font-size: .65rem;
         text-transform: uppercase;
         color: var(--blue);
         letter-spacing: .4px;
     }

     .rep-name {
         display: flex;
         align-items: center;
         gap: 8px;
         color: var(--text);
         font-weight: 500;
     }

     .rep-avatar {
         width: 28px;
         height: 28px;
         border-radius: 50%;
         background: var(--blue);
         color: #fff;
         font-size: .68rem;
         font-weight: 700;
         display: flex;
         align-items: center;
         justify-content: center;
         flex-shrink: 0;
     }

     .rep-cell {
         text-align: center;
         color: #34495e;
     }

     .rep-cell strong {
         color: var(--blue);
     }

     /* ── Today activity ── */
     .visit-row {
         display: flex;
         align-items: center;
         gap: 10px;
         padding: 8px 14px;
         border-bottom: 1px solid #f0f4f8;
     }

     .visit-row:last-child {
         border-bottom: none;
     }

     .visit-row:hover {
         background: #f8fafc;
     }

     .va-avatar {
         width: 30px;
         height: 30px;
         border-radius: 50%;
         background: var(--blue);
         color: #fff;
         font-size: .7rem;
         font-weight: 700;
         display: flex;
         align-items: center;
         justify-content: center;
         flex-shrink: 0;
     }

     .va-name {
         flex: 1;
         font-size: .79rem;
         font-weight: 500;
         color: var(--text);
     }

     .va-meta {
         font-size: .68rem;
         color: var(--muted);
     }

     .va-cnt {
         font-size: .79rem;
         font-weight: 700;
         color: var(--blue);
     }

     /* ── KRA rank ── */
     .kra-row {
         display: flex;
         align-items: center;
         gap: 10px;
         padding: 8px 14px;
         border-bottom: 1px solid #f0f4f8;
     }

     .kra-row:last-child {
         border-bottom: none;
     }

     .kra-row:hover {
         background: #f8fafc;
     }

     .kra-rank {
         width: 24px;
         height: 24px;
         border-radius: 50%;
         background: var(--light);
         color: var(--blue);
         font-size: .68rem;
         font-weight: 700;
         display: flex;
         align-items: center;
         justify-content: center;
         flex-shrink: 0;
     }

     .kra-rank.g {
         background: #fef3c7;
         color: #92400e;
     }

     .kra-rank.s {
         background: #f1f5f9;
         color: #475569;
     }

     .kra-rank.b {
         background: #fef9ee;
         color: #92400e;
     }

     .kra-name {
         flex: 1;
         font-size: .79rem;
         font-weight: 500;
         color: var(--text);
     }

     .kra-score {
         font-size: .78rem;
         font-weight: 700;
         color: var(--blue);
     }

     .kra-pct {
         font-size: .65rem;
         color: var(--muted);
     }

     /* ── Overall target bar ── */
     .target-strip {
         background: #fff;
         border-radius: 10px;
         border: 1px solid var(--border);
         box-shadow: 0 1px 4px rgba(0, 0, 0, .06);
         padding: 14px 18px;
         margin-bottom: 14px;
     }

     .ts-head {
         display: flex;
         justify-content: space-between;
         align-items: center;
         margin-bottom: 8px;
     }

     .ts-title {
         font-size: .82rem;
         font-weight: 700;
         color: var(--text);
     }

     .ts-pct {
         font-size: 1.1rem;
         font-weight: 700;
         color: var(--blue);
     }

     .ts-bar-wrap {
         background: #eef2f6;
         border-radius: 20px;
         height: 9px;
         overflow: hidden;
         margin-bottom: 8px;
     }

     .ts-bar-fill {
         height: 100%;
         border-radius: 20px;
         background: linear-gradient(90deg, var(--blue), var(--blue2));
         transition: width 1.2s ease;
     }

     .ts-meta {
         display: flex;
         flex-wrap: wrap;
         gap: 16px;
     }

     .ts-meta span {
         font-size: .7rem;
         color: var(--muted);
     }

     .ts-meta strong {
         color: var(--text);
         font-weight: 600;
     }

     /* ── Chart ── */
     .chart-wrap {
         padding: 12px 14px;
     }

     /* ── Quick actions ── */
     .quick-grid {
         display: grid;
         grid-template-columns: repeat(8, 1fr);
         gap: 8px;
         padding: 12px;
     }

     @media(max-width:900px) {
         .quick-grid {
             grid-template-columns: repeat(4, 1fr);
         }
     }

     .quick-btn {
         display: flex;
         flex-direction: column;
         align-items: center;
         gap: 5px;
         padding: 10px 4px;
         border-radius: 8px;
         background: var(--light);
         color: var(--blue);
         text-decoration: none;
         font-size: .68rem;
         font-weight: 600;
         transition: background .15s;
         text-align: center;
     }

     .quick-btn:hover {
         background: #d0e7f9;
         color: var(--blue);
     }

     .quick-btn i {
         font-size: 1.15rem;
     }

     /* ── Scheme bar ── */
     .sch-pct-wrap {
         background: #eef2f6;
         border-radius: 20px;
         height: 5px;
         overflow: hidden;
         margin-top: 3px;
     }

     .sch-pct-fill {
         height: 100%;
         border-radius: 20px;
         background: var(--blue);
     }

     .sch-pct-fill.done {
         background: #27ae60;
     }

     .empty-note {
         text-align: center;
         color: var(--muted);
         font-size: .75rem;
         padding: 22px;
     }
 </style>