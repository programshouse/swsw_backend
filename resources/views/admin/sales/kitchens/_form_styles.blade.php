<style>
    .kitchen-form-page {
        --primary: #4948ab;
        --dark: #202b3d;
        --muted: #7b8494;
        --border: #e5e8ee;
        font-family: "Cairo", sans-serif;
    }

    .page-header {
        margin-bottom: 24px;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 14px;
        color: #667085;
        text-decoration: none;
        font-size: 13px;
        font-weight: 700;
    }

    .page-header h1 {
        margin: 0 0 7px;
        color: var(--dark);
        font-size: 28px;
        font-weight: 800;
    }

    .page-header p {
        margin: 0;
        color: var(--muted);
        font-size: 14px;
    }

    .form-card {
        overflow: hidden;
        max-width: 1100px;
        border: 1px solid var(--border);
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 10px 30px rgba(32, 43, 61, .05);
    }

    .form-body {
        padding: 26px;
    }

    .form-section {
        padding-bottom: 28px;
        margin-bottom: 28px;
        border-bottom: 1px solid var(--border);
    }

    .form-section:last-child {
        padding-bottom: 0;
        margin-bottom: 0;
        border-bottom: 0;
    }

    .section-heading {
        margin-bottom: 20px;
    }

    .section-heading h2 {
        margin: 0 0 6px;
        color: var(--dark);
        font-size: 18px;
        font-weight: 800;
    }

    .section-heading p {
        margin: 0;
        color: var(--muted);
        font-size: 12px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .form-group-full {
        grid-column: 1 / -1;
    }

    .form-group label,
    .upload-card label {
        color: #344054;
        font-size: 13px;
        font-weight: 700;
    }

    label span {
        color: #d92d20;
    }

    .form-group input,
    .form-group select {
        width: 100%;
        height: 47px;
        padding: 0 14px;
        border: 1px solid #dfe3ea;
        border-radius: 11px;
        background: #fff;
        color: var(--dark);
        font-family: inherit;
        font-size: 13px;
        outline: none;
        box-sizing: border-box;
    }

    .form-group input:focus,
    .form-group select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(73, 72, 171, .08);
    }

    .images-grid {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 20px;
    }

    .upload-card {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .image-preview {
        height: 180px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border: 2px dashed #d9dde5;
        border-radius: 14px;
        background: #fafbfc;
        color: #a7aeba;
        font-size: 35px;
    }

    .cover-preview {
        height: 180px;
    }

    .image-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .upload-card input[type="file"] {
        padding: 11px;
        border: 1px solid #dfe3ea;
        border-radius: 10px;
        font-family: inherit;
    }

    .validation-alert {
        padding: 15px 18px;
        margin-bottom: 22px;
        border: 1px solid #ffc8c3;
        border-radius: 12px;
        background: #fff1f0;
        color: #b42318;
        font-size: 12px;
    }

    .validation-alert strong {
        display: block;
        margin-bottom: 8px;
    }

    .validation-alert ul {
        margin: 0;
        padding-right: 18px;
    }

    .form-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 11px;
        padding: 19px 26px;
        border-top: 1px solid var(--border);
        background: #fafbfc;
    }

    .cancel-btn,
    .save-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 45px;
        padding: 0 23px;
        border-radius: 11px;
        font-family: inherit;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
    }

    .cancel-btn {
        border: 1px solid #dfe3ea;
        background: #fff;
        color: #344054;
    }

    .save-btn {
        border: 0;
        background: var(--primary);
        color: #fff;
    }

    @media(max-width: 700px) {
        .form-grid,
        .images-grid {
            grid-template-columns: 1fr;
        }

        .form-body {
            padding: 20px 16px;
        }

        .form-actions {
            padding: 16px;
        }

        .cancel-btn,
        .save-btn {
            flex: 1;
            padding: 0 10px;
        }
    }
</style>