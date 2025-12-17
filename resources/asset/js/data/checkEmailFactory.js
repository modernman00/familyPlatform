// data/checkEmailFactory.js
export const makeCheckEmailObj = (kidsCount = 0, siblingCount = 0) => {
  const range = n => Array.from({ length: n }, (_, i) => i + 1);

  return {
    kidEmailInput: range(kidsCount).map(n => `children_email${n}`),
    childrenNameInput: range(kidsCount).map(n => `children_name${n}`),

    siblingEmail: range(siblingCount).map(n => `sibling_email${n}`),
    siblingName: range(siblingCount).map(n => `sibling_name${n}`),
  };
};
