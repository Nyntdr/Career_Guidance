const fs = require('fs');
const path = require('path');

const dir = '/home/nayan/Laravel/Career_Guidance/resources/js/Pages/Admin';

const files = [
    {
        file: 'Skills/Index.vue',
        fields: ['name']
    },
    {
        file: 'SkillCategories/Index.vue',
        fields: ['name']
    },
    {
        file: 'Settings/Index.vue',
        fields: ['site_name', 'currency_symbol']
    },
    {
        file: 'LearningResources/Index.vue',
        fields: ['title']
    },
    {
        file: 'JobPostings/Index.vue',
        fields: ['title']
    },
    {
        file: 'Users/Index.vue',
        fields: ['name', 'email', 'password', 'password_confirmation']
    }
];

files.forEach(({ file, fields }) => {
    const fullPath = path.join(dir, file);
    if (!fs.existsSync(fullPath)) return;
    
    let content = fs.readFileSync(fullPath, 'utf-8');
    
    fields.forEach(field => {
        // regex to find InputLabel that has for="c-field" or "e-field" or "edit-field" or "create-field" or just matches the field
        const idVariations = [`c-${field.replace('_', '-')}`, `e-${field.replace('_', '-')}`, `create-${field.replace('_', '-')}`, `edit-${field.replace('_', '-')}`, field];
        idVariations.forEach(id => {
            const regex = new RegExp(`(<InputLabel for="${id}".*?)(?<!required)\\s*/?>`, 'g');
            content = content.replace(regex, '$1 required />');
            
            const inputRegex1 = new RegExp(`(<TextInput id="${id}".*?)(?<!required)\\s*/?>`, 'g');
            content = content.replace(inputRegex1, '$1 required />');
            
            const inputRegex2 = new RegExp(`(<select id="${id}".*?)(?<!required)\\s*>`, 'g');
            content = content.replace(inputRegex2, '$1 required>');
            
            const inputRegex3 = new RegExp(`(<textarea id="${id}".*?)(?<!required)\\s*/?>`, 'g');
            content = content.replace(inputRegex3, '$1 required />');
            
            const inputRegex4 = new RegExp(`(<input id="${id}".*?)(?<!required)\\s*/?>`, 'g');
            content = content.replace(inputRegex4, '$1 required />');
        });
    });
    
    fs.writeFileSync(fullPath, content);
});
console.log('Done');
