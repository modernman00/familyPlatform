export const dataToCheck = {
	maxLength: {
		id: [
		'first_name','last_name','house_no','street_name','city', 'postcode','mobile','email',
		'employer_name','work_address','monthly_income','bank_name','account_name','account_no','sort_code','password', 'confirm_password', 'job_title'
	 	],
		 max: [15, 15, 5, 30, 20, 7, 10, 30, 30, 50, 5, 10, 30, 8, 6, 50, 50, 15, 30 ],
		// min: [ 1, 1, 1, 1, 2, 2, 1, 5, 3, 5, 10, 7, 3, 10, 3, 3, 4, 8, 6, 7, 7]
	},

	injectData: {
		id : ['loan_calculator_help', 'mobile_help', 'work_address_help', 'monthly_income_help', 'income_and_expenses_help'
		],

		comment: [
			'Our Representative Example: Loan amount £500 repayable over 6 months. Total amount repayable £800.00 in 6 payments of £133.33. Representative 173.8% APR * Illustrative and based on a 30 day month.','Please, do not include the 0. Use this format: 7819111111','Please, enter your full work address.',
			'This income must match you have on your payslip', 'As part of our commitment to responsible lending, it is important for us to understand your regular out-goings when we assessing your application.	In case you share share any cost of the expenses below with any other individuals in your household, please only include your own personal contributions and responsibilities. We will verify the information you are provide in this section so please ensure they are accurate and can be verified. Any false or unverified information you provide will affect our decision to offer you the loan.'
		]
	},

	duplicate: {
		email: 'email_id',
		username: 'username_id'
	},

	password: {
		pwd : 'password_id',
		pwd2: 'confirm_password_id',
		err: 'password_error'
	},

	referral: {
		yes : 'referral_yes',
		no: 'referral_no',
		hidden: 'referral_admin'	
	}
};