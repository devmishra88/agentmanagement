import { useQuery, useMutation, useQueryClient } from "react-query";
import { secureRequest, objectToFormData } from "../utils/axios-utils";

const fetchAreas = () => {
  const reqObject = { Mode: `GetAllArea` };
  const formData = objectToFormData(reqObject);
  return secureRequest({ url: `/area.php`, method: `POST`, data: formData });
};

const addArea = (areadata) => {
  return secureRequest({ url: `/area.php`, method: `POST`, data: areadata });
};

export const useAreasData = (onSuccess, onError) => {
  return useQuery(`areas`, fetchAreas, {
    onSuccess,
    onError,
  });
};

export const useAddAreaData = () => {
  const queryClient = useQueryClient();

  return useMutation(addArea, {
    // onSuccess:(data)=>{
    //   // queryClient.invalidateQueries(`areas`)
    //   queryClient.setQueriesData(`areas`,(oldQueryData)=>{
    //     return{
    //       ...oldQueryData,
    //       data:[...oldQueryData.data, data.data]
    //     }
    //   })
    // }
    /*---------------suppose data saving will not have any error------------*/
    /*---------------Optimistic Updates------------*/
    onMutate: async (newArea) => {
      await queryClient.cancelQueries(`areas`);
      const previousAreaData = queryClient.getQueryData(`areas`);
      /*----------append new data-------*/
      queryClient.setQueriesData(`areas`, (oldQueryData) => {
        return {
          ...oldQueryData,
          data: [
            ...oldQueryData.data,
            { id: oldQueryData?.data?.length + 1, ...newArea },
          ],
        };
      });
      /*----------return previous catched data-------*/
      return {
        previousAreaData,
      };
    },
    onError: (_error, _area, context) => {
      /*----------set previous data if any error from api-------*/
      queryClient.setQueryData(`areas`, context.previousAreaData);
    },
    onSettled: () => {
      /*----------sync data from the db-------*/
      queryClient.invalidateQueries(`areas`);
    },
  });
};
