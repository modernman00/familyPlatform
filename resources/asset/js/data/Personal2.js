export const Personal2 = [
    {
        label: "Mother's maiden name",
        attribute: 'motherMaiden',
        placeholder: 'Mother\'s surname before marriage',
        type: 'text',
        form: 'maiden'
    },
    {
        label: "Number of children",
        attribute: 'kids',
        placeholder: null,
        type: 'select',
        options: [0, 1, 2, 3, 4, 5, 6]
    },
    {
        label: "Gender",
        attribute: 'gender',
        placeholder: null,
        type: 'select',
        options: ['Male', 'Female']
    },
    {
        label: "Number of siblings (Brothers/Sisters)",
        attribute: 'noSiblings',
        placeholder: null,
        type: 'select',
        options: [0, 1, 2, 3, 4, 5, 6]
    },
    {
        label: "Please, upload your picture",
        attribute: 'profileImage',
        placeholder: null,
        type: 'file'
    },
    {
        form: '3-col',
        label: 'Date of Birth:',
        options: {
            label: ['Day', 'Month', 'Year'],
            attribute: ['day', 'month', 'year'],
            placeholder: ['15', 'July', '1982'],
            type: ['number', 'text', 'number']
        }
    }]