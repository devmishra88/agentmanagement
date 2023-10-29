import { useLocation } from 'react-router-dom';

const useQueryParams = () => {
  const location = useLocation();
  const urlQueryParams = new URLSearchParams(location.search);
  let hasMode = false;

  urlQueryParams.forEach((value, key) => {
    if (key === 'mode') {
      hasMode = true;
    }
  });

  const queryParams = (excludeKeys = []) => {
    let queryStr = '';
    const queryObject = {};

    if (!hasMode) {
      urlQueryParams.append('mode', 'add');
      queryObject[`mode`] = `add`;
    }

    for (const [key, value] of urlQueryParams.entries()) {
      if (!excludeKeys.includes(key)) {
        queryStr += `${key}=${encodeURIComponent(value)}&`;
        queryObject[key] = encodeURIComponent(value);
      }
    }

    queryStr = queryStr.replace(/&$/, '').replace(/ /g, '%20');

    return { querystring: queryStr, queryobject: queryObject };
  };

  return queryParams;
};

export default useQueryParams;
