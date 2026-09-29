/**
 * Client-side heuristic validation for resume text files before upload.
 *
 * @param {File} file
 * @returns {Promise<string|null>} Returns error message string if invalid, or null if valid/unknown.
 */
export async function checkResumeFile(file) {
    if (!file) return null;

    const extension = file.name.split('.').pop()?.toLowerCase();

    if (extension !== 'txt') {
        // PDF and DOCX binary files are validated authoritatively on the server.
        return null;
    }

    return new Promise((resolve) => {
        const reader = new FileReader();

        reader.onload = (e) => {
            const text = e.target?.result || '';
            const trimmed = text.trim();

            if (trimmed.length < 200) {
                resolve("That file doesn't look like a resume — it has less than 200 characters of text.");
                return;
            }

            const headings = [
                'experience', 'work experience', 'employment', 'education', 'skills',
                'technical skills', 'summary', 'profile', 'projects', 'certifications',
                'volunteer', 'languages', 'interests', 'contact', 'studies', 'what i do'
            ];

            const lines = text.split('\n').map(l => l.trim().toLowerCase());
            let foundHeading = false;

            for (const line of lines) {
                if (line.length > 0 && line.length <= 60) {
                    if (headings.some(h => line.includes(h))) {
                        foundHeading = true;
                        break;
                    }
                }
            }

            const hasEmail = /[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/.test(text);
            const hasPhone = /(?:\+?\d{1,3}[\s.-]?)?\(?\d{2,4}\)?[\s.-]?\d{3,4}[\s.-]?\d{3,4}/.test(text);

            if (!foundHeading && !hasEmail && !hasPhone) {
                resolve("That file doesn't look like a resume — we couldn't find any experience, education, or skills sections. Check you uploaded the right document.");
                return;
            }

            resolve(null);
        };

        reader.onerror = () => resolve(null);

        // Read up to first 8KB
        const blob = file.slice(0, 8192);
        reader.readAsText(blob);
    });
}
