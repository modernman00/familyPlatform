export const dataToCheck = {
	maxLength: {
		id: [
		'firstName','lastName','alias','spouse', 'fatherName', 'motherName', 'motherMaiden','address','postcode','region','country','mobile','email','favSport','footballTeam','passion','jobTitle','occupation','employerName',
	 	],
		 max: [15, 15, 15, 15, 30, 30, 15, 50, 10,15,15, 13, 45, 25,30,40,30,20, 30   ],
		// min: [ 1, 1, 1, 1, 2, 2, 1, 5, 3, 5, 10, 7, 3, 10, 3, 3, 4, 8, 6, 7, 7]
	},


	duplicate: {
		email: 'email',
		username: 'username'
	},

	password: {
		pwd : 'password',
		pwd2: 'confirm_password'

	},

	familyCheck: {
		father : ["fatherYes", "fatherNo"],
		mother: ["motherYes", "motherNo"],
		spouse: ["spouseYes", "spouseNo"]	
	}
};