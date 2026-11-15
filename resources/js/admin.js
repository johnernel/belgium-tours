import 'trix';
import 'trix/dist/trix.css';

// Block file attachments in the rich-text editor: images go through Admin > Media instead.
document.addEventListener('trix-file-accept', (e) => e.preventDefault());
