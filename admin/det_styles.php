<style>
    .det-wrapper {
        padding: 20px;
        background: #f4f6f9;
        min-height: 100vh;
    }

    .det-back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #1a6ca8;
        font-size: 0.82rem;
        font-weight: 600;
        text-decoration: none;
        margin-bottom: 14px;
    }

    .det-back:hover {
        text-decoration: underline;
    }

    .det-header {
        background: #fff;
        border: 1px solid #dde3ea;
        border-radius: 10px;
        padding: 16px 18px;
        margin-bottom: 18px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .det-header h1 {
        font-size: 1.05rem;
        font-weight: 700;
        color: #1a3f5c;
        margin: 0;
    }

    .det-header .det-sub {
        font-size: 0.78rem;
        color: #8a9ab0;
        margin-top: 2px;
    }

    .det-summary {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .det-summary .pill {
        background: #eaf2fb;
        border: 1px solid #b8d4ed;
        border-radius: 20px;
        padding: 6px 14px;
        font-size: 0.8rem;
        color: #1a6ca8;
        font-weight: 600;
    }

    .det-section {
        background: #fff;
        border: 1px solid #dde3ea;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 1px 4px rgba(0, 0, 0, .06);
        margin-bottom: 16px;
    }

    .det-section-head {
        background: #1a6ca8;
        padding: 10px 14px;
    }

    .det-section-head span {
        color: #fff;
        font-size: 0.82rem;
        font-weight: 600;
        letter-spacing: 0.2px;
    }

    table.det-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.83rem;
    }

    table.det-table th {
        background: #eaf2fb;
        color: #1a6ca8;
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        padding: 9px 10px;
        border-bottom: 2px solid #b8d4ed;
        text-align: left;
        white-space: nowrap;
    }

    table.det-table td {
        padding: 8px 10px;
        border-bottom: 1px solid #eef2f6;
        color: #34495e;
        white-space: nowrap;
    }

    table.det-table tr:hover td {
        background: #f8fafc;
    }

    table.det-table tr:last-child td {
        border-bottom: none;
    }

    .num-cell {
        text-align: right;
    }

    .center-cell {
        text-align: center;
    }

    .status-active {
        color: #27ae60;
        font-weight: 600;
    }

    .status-inactive {
        color: #e74c3c;
        font-weight: 600;
    }

    .det-empty {
        text-align: center;
        padding: 26px;
        color: #b0bec5;
        font-size: 0.85rem;
    }

    .badge-class {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 12px;
        background: #f1f4f8;
        color: #5a6c80;
        font-size: 0.72rem;
        font-weight: 600;
    }
</style>