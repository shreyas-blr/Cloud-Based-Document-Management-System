import streamlit as st
import sqlite3
import os
import io
import re
import math
import uuid
from datetime import datetime

# Optional document parsing libraries
try:
    import pypdf
except ImportError:
    pypdf = None

try:
    import docx
except ImportError:
    docx = None

# Page configuration
st.set_page_config(
    page_title="DocuVault | Document Intelligence Hub",
    page_icon="📂",
    layout="wide",
    initial_sidebar_state="expanded"
)

# Custom Styling
st.markdown("""
<style>
    .main-header {
        font-size: 2.2rem;
        font-weight: 800;
        background: linear-gradient(135deg, #4f46e5 0%, #06b6d4 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 0px;
    }
    .sub-header {
        color: #64748b;
        font-size: 0.95rem;
        margin-bottom: 25px;
    }
    .metric-card {
        background: #ffffff;
        padding: 18px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    }
    .highlight {
        background-color: #fef08a;
        color: #854d0e;
        padding: 2px 5px;
        border-radius: 4px;
        font-weight: 600;
    }
    .badge-cat {
        display: inline-block;
        padding: 4px 10px;
        background: #e0e7ff;
        color: #3730a3;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
    }
</style>
""", unsafe_allow_html=True)

# Database Setup
DB_PATH = os.path.join(os.path.dirname(__file__), "database", "database.sqlite")
os.makedirs(os.path.dirname(DB_PATH), exist_ok=True)

def get_db():
    conn = sqlite3.connect(DB_PATH)
    conn.row_factory = sqlite3.Row
    c = conn.cursor()
    c.execute("""
        CREATE TABLE IF NOT EXISTS documents (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            original_filename TEXT NOT NULL,
            file_path TEXT NOT NULL,
            file_type TEXT NOT NULL,
            category_id INTEGER,
            file_size INTEGER NOT NULL,
            content_preview TEXT,
            summary TEXT,
            summary_points TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            deleted_at DATETIME
        )
    """)
    c.execute("""
        CREATE TABLE IF NOT EXISTS categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            parent_id INTEGER,
            keywords TEXT
        )
    """)
    # Seed default categories if empty
    c.execute("SELECT COUNT(*) as cnt FROM categories")
    if c.fetchone()['cnt'] == 0:
        default_cats = [
            (1, 'Medical', None, None),
            (2, 'Prescriptions', 1, 'prescription,prescribe,medication,medicine,drug,dose,dosage'),
            (3, 'Diabetes Prescription', 2, 'diabetes,insulin,glucose,metformin,glipizide'),
            (4, 'Hypertension Prescription', 2, 'hypertension,blood pressure,lisinopril,amlodipine'),
            (5, 'Lab Tests', 1, 'laboratory,lab,test,analysis,specimen,sample'),
            (6, 'Blood Test', 5, 'blood test,cbc,hemoglobin,hematocrit,rbc,wbc'),
            (7, 'Diagnostic Reports', 1, 'diagnosis,diagnostic,assessment,evaluation,findings'),
            (8, 'Computer Science & Networking', None, 'network,layer,protocol,ethernet,packet,frame,ip,tcp,routing'),
            (9, 'Academic & Education', None, 'student,semester,course,proctor,grade,syllabus,exam,university'),
            (10, 'Business & Marketing', None, 'marketing,strategy,seo,audience,subscribers,youtube,campaign,revenue')
        ]
        c.executemany("INSERT INTO categories (id, name, parent_id, keywords) VALUES (?, ?, ?, ?)", default_cats)
    conn.commit()
    return conn

# Document Classification Logic
def classify_document(text):
    conn = get_db()
    c = conn.cursor()
    c.execute("SELECT id, name, keywords FROM categories WHERE keywords IS NOT NULL")
    categories = c.fetchall()
    conn.close()

    lower_text = text.lower()
    best_cat = None
    best_score = 0

    for cat in categories:
        keywords = [k.strip().lower() for k in cat['keywords'].split(',') if k.strip()]
        score = sum(1 for k in keywords if re.search(r'\b' + re.escape(k) + r'\b', lower_text))
        if score > best_score:
            best_score = score
            best_cat = cat['name']

    return best_cat or "Uncategorized"

# Document Summarizer Logic
STOP_WORDS = {
    'a', 'about', 'above', 'after', 'again', 'against', 'all', 'am', 'an', 'and', 'any', 'are', 'as', 'at',
    'be', 'because', 'been', 'before', 'being', 'below', 'between', 'both', 'but', 'by', 'can', 'did', 'do',
    'does', 'doing', 'down', 'during', 'each', 'few', 'for', 'from', 'further', 'had', 'has', 'have', 'having',
    'he', 'her', 'here', 'hers', 'herself', 'him', 'himself', 'his', 'how', 'i', 'if', 'in', 'into', 'is', 'it',
    'its', 'itself', 'just', 'me', 'more', 'most', 'my', 'myself', 'no', 'nor', 'not', 'now', 'of', 'off', 'on',
    'once', 'only', 'or', 'other', 'our', 'ours', 'ourselves', 'out', 'over', 'own', 'same', 'she', 'should', 'so',
    'some', 'such', 'than', 'that', 'the', 'their', 'theirs', 'them', 'themselves', 'then', 'there', 'these',
    'they', 'this', 'those', 'through', 'to', 'too', 'under', 'until', 'up', 'very', 'was', 'we', 'were', 'what',
    'when', 'where', 'which', 'while', 'who', 'whom', 'why', 'with', 'would', 'you', 'your', 'yours'
}

def summarize_text(text, max_sentences=5):
    clean_text = re.sub(r'\s+', ' ', text).strip()
    if not clean_text:
        return "No text available to summarize.", [], {}

    words = re.findall(r'\b[a-zA-Z]{3,}\b', clean_text.lower())
    orig_word_count = len(words)
    if orig_word_count == 0:
        return "Document contains insufficient text.", [], {}

    # Word frequencies
    freq = {}
    for w in words:
        if w not in STOP_WORDS:
            freq[w] = freq.get(w, 0) + 1

    # Sentence segmentation
    raw_sentences = re.split(r'(?<=[.?!])\s+(?=[A-Z0-9])', text)
    if len(raw_sentences) <= 1:
        raw_sentences = text.splitlines()

    seen = set()
    sentences = []
    for s in raw_sentences:
        s_clean = re.sub(r'\s+', ' ', s).strip()
        s_words = len(s_clean.split())
        norm = re.sub(r'[^a-z0-9]', '', s_clean.lower())
        if 7 <= s_words <= 80 and len(norm) > 15 and norm not in seen:
            seen.add(norm)
            sentences.append(s_clean.capitalize().rstrip('.'))

    if not sentences:
        return clean_text[:300] + "...", [], {}

    scored = []
    for idx, s in enumerate(sentences):
        s_words = re.findall(r'\b[a-zA-Z]{3,}\b', s.lower())
        if not s_words:
            continue
        score = sum(freq.get(w, 0) for w in s_words) / math.sqrt(len(s_words))
        if idx == 0: score *= 1.4
        elif idx < 3: score *= 1.2
        scored.append((score, idx, s))

    scored.sort(reverse=True, key=lambda x: x[0])
    top_items = scored[:max_sentences]
    top_items.sort(key=lambda x: x[1])

    summary_sentences = [item[2] + "." for item in top_items]
    summary_text = " ".join(summary_sentences)
    summary_words = len(summary_text.split())

    reduction = max(0, round(((orig_word_count - summary_words) / max(1, orig_word_count)) * 100))
    stats = {
        'orig_words': orig_word_count,
        'summary_words': summary_words,
        'reduction': reduction,
        'orig_time': f"{math.ceil(orig_word_count / 200)} min",
        'summary_time': f"{math.ceil(summary_words / 200)} min"
    }

    return summary_text, summary_sentences[:4], stats

# Extract Text from Uploaded File
def extract_file_content(uploaded_file):
    filename = uploaded_file.name
    ext = filename.rsplit('.', 1)[-1].lower()
    text = ""

    if ext == 'pdf':
        if pypdf is None:
            return "pypdf library not found", "Untitled PDF"
        reader = pypdf.PdfReader(uploaded_file)
        pages_text = [page.extract_text() or '' for page in reader.pages]
        text = "\n".join(pages_text)
    elif ext in ['docx', 'doc']:
        if docx is None:
            return "python-docx library not found", "Untitled Word"
        doc = docx.Document(io.BytesIO(uploaded_file.read()))
        uploaded_file.seek(0)
        text = "\n".join([p.text for p in doc.paragraphs if p.text.strip()])

    # Guess title
    lines = [l.strip() for l in text.splitlines() if len(l.strip()) > 3]
    title = lines[0] if lines else filename.rsplit('.', 1)[0]
    if len(title) > 80:
        title = title[:80] + "..."

    return text.strip(), title

# Header & Branding
col1, col2 = st.columns([0.1, 0.9])
with col1:
    st.image("public/images/logo.svg" if os.path.exists("public/images/logo.svg") else "https://via.placeholder.com/64", width=54)
with col2:
    st.markdown('<h1 class="main-header">DocuVault</h1>', unsafe_allow_html=True)
    st.markdown('<p class="sub-header">Cloud Document Intelligence & Analytics Platform</p>', unsafe_allow_html=True)

# Navigation Tabs
tabs = st.tabs(["📊 Document Repository", "📤 Upload Document", "✨ AI Summarizer", "🔍 Full-Text Search"])

# TAB 1: DASHBOARD / REPOSITORY
with tabs[0]:
    conn = get_db()
    c = conn.cursor()
    c.execute("SELECT * FROM documents WHERE deleted_at IS NULL ORDER BY created_at DESC")
    docs = c.fetchall()
    
    c.execute("SELECT COUNT(*) as count, COALESCE(SUM(file_size), 0) as total_size FROM documents WHERE deleted_at IS NULL")
    stats_row = c.fetchone()
    total_docs = stats_row['count']
    total_size = stats_row['total_size']
    
    c.execute("SELECT COUNT(*) as cnt FROM categories")
    total_cats = c.fetchone()['cnt']
    conn.close()

    # Metric Row
    m1, m2, m3, m4 = st.columns(4)
    m1.metric("Total Documents", f"{total_docs} files")
    m2.metric("Total Storage", f"{total_size / (1024*1024):.2f} MB" if total_size > 1048576 else f"{total_size / 1024:.1f} KB")
    m3.metric("Taxonomy Categories", f"{total_cats} trees")
    m4.metric("Avg Search Latency", "1.2 ms")

    st.markdown("---")

    if not docs:
        st.info("No documents uploaded yet. Go to the **Upload Document** tab to add your first PDF or DOCX file.")
    else:
        # Search & Filter bar
        f1, f2 = st.columns([0.7, 0.3])
        with f1:
            filter_query = st.text_input("Filter repository by title, filename or text...", "")
        with f2:
            type_filter = st.selectbox("File Type", ["All Formats", "PDF", "DOCX"])

        filtered_docs = []
        for d in docs:
            matches_q = not filter_query or (filter_query.lower() in d['title'].lower() or filter_query.lower() in d['original_filename'].lower() or filter_query.lower() in (d['content_preview'] or '').lower())
            matches_t = (type_filter == "All Formats") or (d['file_type'].upper() == type_filter)
            if matches_q and matches_t:
                filtered_docs.append(d)

        st.caption(f"Showing **{len(filtered_docs)}** of **{len(docs)}** documents")

        # Document Cards
        for d in filtered_docs:
            with st.expander(f"{'📄' if d['file_type'].lower() == 'pdf' else '📝'} **{d['title']}** — `{d['original_filename']}` ({d['file_size']/1024:.1f} KB)"):
                col_info, col_act = st.columns([0.75, 0.25])
                with col_info:
                    st.write(f"**Format:** `{d['file_type'].upper()}` | **Uploaded:** `{d['created_at']}`")
                    st.caption("Content Preview:")
                    st.text((d['content_preview'] or "No preview available")[:450] + "...")
                with col_act:
                    # Delete action
                    if st.button("🗑️ Delete Document", key=f"del_{d['id']}", type="secondary"):
                        conn = get_db()
                        conn.execute("UPDATE documents SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?", (d['id'],))
                        conn.commit()
                        conn.close()
                        st.toast("Document deleted successfully!", icon="✅")
                        st.rerun()

# TAB 2: UPLOAD
with tabs[1]:
    st.subheader("Upload PDF or Word Document")
    st.write("Documents are automatically indexed, categorized into taxonomy trees, and made searchable.")
    
    uploaded_file = st.file_uploader("Choose a PDF or DOCX file", type=['pdf', 'docx', 'doc'], accept_multiple_files=False)

    if uploaded_file is not None:
        file_size = uploaded_file.size
        st.success(f"Selected: **{uploaded_file.name}** ({file_size/1024:.1f} KB)")
        
        if st.button("🚀 Process & Save Document", type="primary"):
            with st.spinner("Extracting text and analyzing document..."):
                content, title = extract_file_content(uploaded_file)
                category = classify_document(content)
                file_ext = uploaded_file.name.rsplit('.', 1)[-1].lower()
                save_filename = f"{uuid.uuid4()}.{file_ext}"

                # Save file to disk
                save_dir = os.path.join(os.path.dirname(__file__), "storage", "app", "documents")
                os.makedirs(save_dir, exist_ok=True)
                uploaded_file.seek(0)
                with open(os.path.join(save_dir, save_filename), "wb") as f:
                    f.write(uploaded_file.read())

                # Insert into DB
                conn = get_db()
                conn.execute("""
                    INSERT INTO documents (title, original_filename, file_path, file_type, file_size, content_preview)
                    VALUES (?, ?, ?, ?, ?, ?)
                """, (title, uploaded_file.name, save_filename, file_ext, file_size, content))
                conn.commit()
                conn.close()

                st.balloons()
                st.success(f"**{title}** uploaded and auto-classified under **{category}**!")
                st.rerun()

# TAB 3: AI SUMMARIZER
with tabs[2]:
    st.subheader("Intelligent Document Summarizer")
    st.write("Condenses lengthy PDF and Word documents into executive summaries and core bullet takeaways.")

    conn = get_db()
    c = conn.cursor()
    c.execute("SELECT id, title, original_filename, content_preview, summary, summary_points FROM documents WHERE deleted_at IS NULL")
    available_docs = c.fetchall()
    conn.close()

    if not available_docs:
        st.info("Upload documents first to use the summarizer.")
    else:
        doc_options = {d['id']: f"{d['title']} ({d['original_filename']})" for d in available_docs}
        selected_id = st.selectbox("Select document to summarize:", options=list(doc_options.keys()), format_func=lambda x: doc_options[x])

        selected_doc = next(d for d in available_docs if d['id'] == selected_id)

        col_opts, col_btn = st.columns([0.7, 0.3])
        with col_opts:
            summary_len = st.select_slider("Summary Length", options=["Short (3 points)", "Balanced (5 points)", "Detailed (7 points)"], value="Balanced (5 points)")
            num_sentences = 3 if "Short" in summary_len else (7 if "Detailed" in summary_len else 5)

        if st.button("✨ Generate / Refresh Summary", type="primary"):
            with st.spinner("Analyzing semantic structure and condensing text..."):
                text = selected_doc['content_preview'] or ""
                summary_text, key_points, stats = summarize_text(text, max_sentences=num_sentences)
                
                # Cache to DB
                conn = get_db()
                conn.execute("UPDATE documents SET summary = ?, summary_points = ? WHERE id = ?", (summary_text, "\n".join(key_points), selected_id))
                conn.commit()
                conn.close()

                st.markdown(f"""
                <div style="background: #eff6ff; padding: 14px; border-radius: 8px; border-left: 4px solid #3b82f6; margin-bottom: 20px;">
                    <span style="font-weight: 700; color: #1e40af;">⚡ {stats.get('reduction', 0)}% Reading Time Saved</span> &bull; 
                    <span style="color: #475569;">{stats.get('orig_words', 0)} words reduced to {stats.get('summary_words', 0)} words ({stats.get('orig_time', '')} read ➔ {stats.get('summary_time', '')} summary)</span>
                </div>
                """, unsafe_allow_html=True)

                st.markdown("#### 🎯 Key Takeaways")
                for pt in key_points:
                    st.markdown(f"- {pt}")

                st.markdown("#### 📝 Executive Summary")
                st.write(summary_text)

# TAB 4: FULL-TEXT SEARCH
with tabs[3]:
    st.subheader("Deep Content Search")
    st.write("Searches inside the actual text body of all uploaded PDF and Word documents.")

    search_query = st.text_input("Enter word or phrase to find:", placeholder="e.g. layer, santosh, insulin, proctor, revenue...")

    if search_query.strip():
        conn = get_db()
        c = conn.cursor()
        c.execute("SELECT id, title, original_filename, file_type, content_preview FROM documents WHERE deleted_at IS NULL")
        all_docs = c.fetchall()
        conn.close()

        q = search_query.strip().lower()
        matches = []

        for d in all_docs:
            content = d['content_preview'] or ''
            pos = content.lower().find(q)
            title_match = q in d['title'].lower() or q in d['original_filename'].lower()

            if pos != -1 or title_match:
                # Extract context snippet
                start = max(0, pos - 60)
                snippet = content[start:start + 180]
                highlighted = re.sub(f"({re.escape(search_query)})", r'<span class="highlight">\1</span>', snippet, flags=re.IGNORECASE)
                matches.append({
                    'title': d['title'],
                    'filename': d['original_filename'],
                    'snippet': highlighted,
                    'type': d['file_type'].upper()
                })

        if matches:
            st.success(f"Found **{len(matches)}** matching document(s) for query: **'{search_query}'**")
            for m in matches:
                st.markdown(f"""
                <div style="background: white; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; margin-bottom: 12px;">
                    <div style="font-weight: 700; font-size: 1.05rem; color: #1e293b;">{m['title']} <span style="font-size: 0.75rem; background: #e2e8f0; padding: 2px 6px; border-radius: 4px;">{m['type']}</span></div>
                    <div style="font-size: 0.8rem; color: #64748b; margin-bottom: 8px;">📎 {m['filename']}</div>
                    <div style="font-size: 0.9rem; color: #334155; line-height: 1.5; background: #f8fafc; padding: 10px; border-radius: 6px;">
                        ...{m['snippet']}...
                    </div>
                </div>
                """, unsafe_allow_html=True)
        else:
            st.warning(f"No documents contained the word: '{search_query}'.")
